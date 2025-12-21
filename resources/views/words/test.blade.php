@extends('layouts.engram')

@section('title', 'Тест слів')

@section('content')
  <div class="space-y-8" x-data>
    <style>
      @keyframes fade-in-soft {
        from { opacity: 0; transform: translateY(6px); }
        to { opacity: 1; transform: translateY(0); }
      }

      @keyframes pop-in {
        0% { transform: scale(0.97); opacity: 0; }
        60% { transform: scale(1.02); opacity: 1; }
        100% { transform: scale(1); }
      }

      @keyframes choice-glow {
        0% { transform: scale(1); box-shadow: 0 10px 30px -12px rgba(0, 0, 0, 0.25); }
        50% { transform: scale(1.02); box-shadow: 0 14px 40px -16px rgba(0, 0, 0, 0.3); }
        100% { transform: scale(1); box-shadow: 0 10px 30px -12px rgba(0, 0, 0, 0.25); }
      }

      @keyframes choice-shake {
        0%, 100% { transform: translateX(0); }
        15% { transform: translateX(-6px); }
        30% { transform: translateX(6px); }
        45% { transform: translateX(-5px); }
        60% { transform: translateX(5px); }
        75% { transform: translateX(-3px); }
        90% { transform: translateX(3px); }
      }

      .animate-soft { animation: fade-in-soft 280ms ease; }
      .animate-pop { animation: pop-in 220ms ease; }
      .animate-choice { animation: choice-glow 1s ease; }
      .animate-shake { animation: choice-shake 600ms ease; }

      .choice-correct {
        border-color: rgba(34, 197, 94, 0.35);
        background: linear-gradient(135deg, rgba(34, 197, 94, 0.12), rgba(34, 197, 94, 0.04));
        color: rgb(21, 128, 61);
      }

      .choice-wrong {
        border-color: rgba(239, 68, 68, 0.35);
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.12), rgba(239, 68, 68, 0.04));
        color: rgb(185, 28, 28);
      }

      .modal-backdrop {
        background: radial-gradient(circle at center, rgba(15, 23, 42, 0.16), rgba(15, 23, 42, 0.55));
        backdrop-filter: blur(8px);
      }
    </style>
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
      <div class="space-y-1">
        <p class="text-sm text-muted-foreground">Практика перекладу · Активна мова: <span class="font-semibold text-primary">{{ strtoupper($activeLang) }}</span></p>
        <h1 class="text-3xl font-semibold text-foreground">Швидкий тест слів</h1>
        <p class="text-muted-foreground max-w-2xl">Виберіть правильний переклад без перезавантаження сторінки. Прогрес зберігається автоматично — можна оновити сторінку і продовжити з того ж місця.</p>
      </div>
      <div class="flex flex-wrap gap-3">
        <button id="reset-btn" class="inline-flex items-center gap-2 rounded-xl border border-border bg-background px-4 py-2 text-sm font-semibold text-foreground shadow-sm transition hover:-translate-y-0.5 hover:shadow">
          <span>Почати заново</span>
        </button>
      </div>
    </div>

    <div class="grid gap-4 md:grid-cols-[1.6fr_1fr]">
      <div class="rounded-2xl bg-card p-6 shadow-soft border border-border/70" id="question-card">
        <div class="flex items-center justify-between gap-3">
          <div class="inline-flex items-center gap-2 rounded-full bg-primary/10 px-3 py-1 text-sm font-medium text-primary">
            <span class="h-2 w-2 rounded-full bg-primary"></span>
            <span id="question-label">Завантаження питання…</span>
          </div>
          <div class="text-sm text-muted-foreground" id="queue-counter"></div>
        </div>

        <div class="mt-6 space-y-6" id="question-wrapper">
          <div class="space-y-2">
            <p class="text-sm uppercase tracking-[0.08em] text-muted-foreground">Підказка</p>
            <div id="question-prompt" class="text-3xl font-semibold text-foreground">Зачекайте...</div>
            <p id="question-tags" class="text-sm text-muted-foreground"></p>
          </div>

          <div class="grid gap-3 md:grid-cols-2" id="options"></div>
        </div>

        <div id="empty-state" class="hidden text-muted-foreground">
          <p class="text-lg font-semibold text-foreground">Питання не знайдено</p>
          <p>Додайте більше слів з перекладом на активну мову або натисніть «Почати заново», щоб спробувати ще раз.</p>
        </div>
      </div>

      <div class="space-y-4">
        <div class="rounded-2xl bg-card p-5 shadow-soft border border-border/70">
          <div class="flex items-center justify-between">
            <p class="text-sm font-semibold text-muted-foreground">Прогрес</p>
            <span class="rounded-full bg-muted px-3 py-1 text-xs font-semibold text-muted-foreground" id="percentage">0%</span>
          </div>
          <div class="mt-3 h-3 rounded-full bg-muted">
            <div id="progress-bar" class="h-3 rounded-full bg-primary transition-all duration-500" style="width: 0%"></div>
          </div>
          <dl class="mt-4 grid grid-cols-3 gap-3 text-sm">
            <div class="rounded-xl bg-muted px-3 py-2">
              <dt class="text-muted-foreground">Усього</dt>
              <dd id="stat-total" class="text-lg font-semibold text-foreground">0</dd>
            </div>
            <div class="rounded-xl bg-success/10 px-3 py-2 text-success">
              <dt class="text-sm">Правильно</dt>
              <dd id="stat-correct" class="text-lg font-semibold">0</dd>
            </div>
            <div class="rounded-xl bg-destructive/10 px-3 py-2 text-destructive">
              <dt class="text-sm">Помилки</dt>
              <dd id="stat-wrong" class="text-lg font-semibold">0</dd>
            </div>
          </dl>
        </div>

        <div class="rounded-2xl bg-card p-5 shadow-soft border border-border/70" id="feedback" hidden>
          <div id="feedback-chip" class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-semibold"></div>
          <div class="mt-3 space-y-2">
            <p id="feedback-title" class="text-xl font-semibold"></p>
            <p id="feedback-body" class="text-muted-foreground"></p>
          </div>
        </div>

        <div class="rounded-2xl bg-card p-5 shadow-soft border border-dashed border-border/80" id="completion" hidden>
          <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 text-primary">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>
            </div>
            <div>
              <p class="text-sm font-semibold text-muted-foreground">Тест завершено</p>
              <p class="text-lg font-semibold text-foreground">Всі доступні слова опрацьовано</p>
            </div>
          </div>
          <p class="mt-3 text-muted-foreground">Можете почати заново, щоб повторити матеріал.</p>
        </div>
      </div>
    </div>

    <div id="failure-modal" class="fixed inset-0 z-50 hidden items-center justify-center">
      <div class="modal-backdrop absolute inset-0"></div>
      <div id="failure-card" class="relative mx-4 w-full max-w-xl rounded-2xl border border-destructive/30 bg-card p-6 shadow-2xl animate-pop">
        <div class="flex items-start gap-4">
          <div class="flex h-12 w-12 items-center justify-center rounded-full bg-destructive/10 text-destructive">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4m0 4h.01M4.93 4.93l14.14 14.14"/></svg>
          </div>
          <div class="space-y-2">
            <div>
              <p class="text-sm font-semibold text-muted-foreground">Тест не пройдено</p>
              <p class="text-2xl font-semibold text-foreground">Перевищено ліміт у 3 помилки</p>
            </div>
            <p class="text-muted-foreground">Ви зробили три помилки поспіль. Натисніть «Спробувати ще раз», щоб пройти тест повторно.</p>
            <div class="flex flex-wrap gap-3 pt-2">
              <button id="retry-btn" class="inline-flex items-center gap-2 rounded-xl border border-destructive/50 bg-destructive/10 px-4 py-2 text-sm font-semibold text-destructive shadow-sm transition hover:-translate-y-0.5 hover:shadow">
                Спробувати ще раз
              </button>
              <button id="close-failure" class="inline-flex items-center gap-2 rounded-xl border border-border bg-background px-4 py-2 text-sm font-semibold text-foreground shadow-sm transition hover:-translate-y-0.5 hover:shadow">
                Закрити
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const stateUrl = "{{ route('words.test.state') }}";
      const checkUrl = "{{ route('words.test.check') }}";
      const resetUrl = "{{ route('words.test.reset') }}";
      const csrfToken = '{{ csrf_token() }}';

      const questionLabel = document.getElementById('question-label');
      const queueCounter = document.getElementById('queue-counter');
      const questionPrompt = document.getElementById('question-prompt');
      const questionTags = document.getElementById('question-tags');
      const optionsWrapper = document.getElementById('options');
      const percentageEl = document.getElementById('percentage');
      const progressBar = document.getElementById('progress-bar');
      const statTotal = document.getElementById('stat-total');
      const statCorrect = document.getElementById('stat-correct');
      const statWrong = document.getElementById('stat-wrong');
      const feedback = document.getElementById('feedback');
      const feedbackChip = document.getElementById('feedback-chip');
      const feedbackTitle = document.getElementById('feedback-title');
      const feedbackBody = document.getElementById('feedback-body');
      const completion = document.getElementById('completion');
      const failureModal = document.getElementById('failure-modal');
      const failureCard = document.getElementById('failure-card');
      const questionWrapper = document.getElementById('question-wrapper');
      const emptyState = document.getElementById('empty-state');
      const resetBtn = document.getElementById('reset-btn');
      const retryBtn = document.getElementById('retry-btn');
      const closeFailure = document.getElementById('close-failure');

      let currentQuestion = null;
      let loading = false;

      function animate(el, className = 'animate-soft') {
        if (!el) return;
        el.classList.remove(className);
        // force reflow
        void el.offsetWidth;
        el.classList.add(className);
      }

      async function fetchState() {
        loading = true;
        toggleOptions(false);
        const response = await fetch(stateUrl, { headers: { 'Accept': 'application/json' } });
        const data = await response.json();
        loading = false;
        updateState(data);
      }

      function updateState(data) {
        renderStats(data.stats, data.percentage, data.totalCount);
        completion.hidden = !data.completed;
        toggleFailureModal(data.failed);

        if (data.failed) {
          currentQuestion = null;
          questionWrapper.classList.add('hidden');
          emptyState.classList.add('hidden');
          questionLabel.textContent = 'Тест не пройдено';
          queueCounter.textContent = '';
          toggleOptions(false);
          return;
        }

        if (!data.question) {
          currentQuestion = null;
          questionWrapper.classList.add('hidden');
          emptyState.classList.remove('hidden');
          queueCounter.textContent = '';
          questionLabel.textContent = data.completed ? 'Все пройдено' : 'Немає питань';
          return;
        }

        emptyState.classList.add('hidden');
        questionWrapper.classList.remove('hidden');
        currentQuestion = data.question;
        renderQuestion(data.question, data.totalCount, data.stats.total);
        feedback.hidden = true;
        completion.hidden = true;
      }

      function renderStats(stats, percentage, totalCount) {
        statTotal.textContent = `${stats.total} / ${totalCount}`;
        statCorrect.textContent = stats.correct;
        statWrong.textContent = stats.wrong;
        percentageEl.textContent = `${percentage}%`;
        progressBar.style.width = `${Math.min(percentage, 100)}%`;
      }

      function renderQuestion(question, totalCount, totalAnswered) {
        questionLabel.textContent = question.questionType === 'en_to_uk'
          ? 'Обрати переклад українською'
          : 'Обрати слово англійською';

        queueCounter.textContent = `Питання ${totalAnswered + 1} з ${totalCount}`;
        questionPrompt.textContent = question.prompt;
        questionTags.textContent = (question.tags || []).join(', ');

        optionsWrapper.innerHTML = '';
        question.options.forEach((option) => {
          const btn = document.createElement('button');
          btn.type = 'button';
          btn.className = 'flex items-center justify-between gap-3 rounded-xl border border-border/80 bg-muted px-4 py-3 text-left text-foreground shadow-sm transition hover:-translate-y-0.5 hover:shadow focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary';
          btn.textContent = option;
          btn.dataset.value = option;
          btn.addEventListener('click', () => submitAnswer(option, btn));
          optionsWrapper.appendChild(btn);
          animate(btn, 'animate-soft');
        });
        toggleOptions(!loading);
        animate(questionPrompt);
        animate(optionsWrapper);
      }

      function toggleOptions(enabled) {
        optionsWrapper.querySelectorAll('button').forEach((btn) => {
          btn.disabled = !enabled;
          btn.classList.toggle('opacity-60', !enabled);
          btn.classList.toggle('cursor-not-allowed', !enabled);
        });
      }

      async function submitAnswer(answer, selectedButton) {
        if (!currentQuestion || loading) return;
        loading = true;
        toggleOptions(false);

        const formData = new FormData();
        formData.append('word_id', currentQuestion.word_id);
        formData.append('answer', answer);

        const response = await fetch(checkUrl, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
          },
          body: formData,
        });

        if (!response.ok) {
          loading = false;
          toggleOptions(true);
          return;
        }

        const data = await response.json();
        loading = false;

        highlightSelection(selectedButton, data.result?.isCorrect);

        setTimeout(() => {
          renderStats(data.stats, data.percentage, data.totalCount);
          showFeedback(data.result);

          currentQuestion = data.question;
          completion.hidden = !data.completed;
          toggleFailureModal(data.failed);

          if (data.failed) {
            questionWrapper.classList.add('hidden');
            emptyState.classList.add('hidden');
            questionLabel.textContent = 'Тест не пройдено';
            queueCounter.textContent = '';
            toggleOptions(false);
            return;
          }

          if (data.completed || !data.question) {
            completion.hidden = false;
            questionWrapper.classList.add('hidden');
            emptyState.classList.remove('hidden');
            questionLabel.textContent = 'Все пройдено';
            queueCounter.textContent = '';
          } else {
            renderQuestion(data.question, data.totalCount, data.stats.total);
          }
          toggleOptions(!!currentQuestion);
        }, 1000);
      }

      function toggleFailureModal(show) {
        failureModal.classList.toggle('hidden', !show);
        if (show) {
          animate(failureCard, 'animate-pop');
        }
      }

      function highlightSelection(button, isCorrect) {
        if (!button) return;
        button.classList.remove('choice-correct', 'choice-wrong', 'animate-choice', 'animate-shake');
        void button.offsetWidth;
        if (isCorrect) {
          button.classList.add('choice-correct', 'animate-choice');
        } else {
          button.classList.add('choice-wrong', 'animate-choice', 'animate-shake');
        }
        setTimeout(() => {
          button.classList.remove('choice-correct', 'choice-wrong', 'animate-choice', 'animate-shake');
        }, 1000);
      }

      function showFeedback(result) {
        feedback.hidden = false;
        animate(feedback, 'animate-pop');
        const isCorrect = result.isCorrect;
        feedbackChip.textContent = isCorrect ? 'Правильно' : 'Помилка';
        feedbackChip.className = `inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-semibold ${isCorrect ? 'bg-success/10 text-success' : 'bg-destructive/10 text-destructive'}`;

        if (result.questionType === 'en_to_uk') {
          feedbackTitle.textContent = `${result.word} → ${result.correctAnswer}`;
        } else {
          feedbackTitle.textContent = `${result.translation} → ${result.correctAnswer}`;
        }

        feedbackBody.textContent = isCorrect
          ? 'Чудово! Продовжуйте у тому ж дусі.'
          : 'Зверніть увагу на правильний варіант і спробуйте ще раз.';
      }

      async function resetTest(button) {
        if (button) {
          button.disabled = true;
          button.classList.add('opacity-60');
        }
        const response = await fetch(resetUrl, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
          },
        });

        const data = await response.json();
        updateState(data);
        if (button) {
          button.disabled = false;
          button.classList.remove('opacity-60');
        }
      }

      resetBtn.addEventListener('click', () => resetTest(resetBtn));
      retryBtn.addEventListener('click', () => resetTest(retryBtn));
      closeFailure.addEventListener('click', () => toggleFailureModal(false));

      fetchState();
    });
  </script>
@endsection
