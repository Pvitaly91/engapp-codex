@extends('layouts.engram')

@section('title', __('words_test.page_title'))

@section('content')
  @php
    $difficulty = $difficulty ?? 'easy';
    $tabs = [
        ['label' => __('words_test.difficulties.easy'), 'difficulty' => 'easy', 'href' => route('words.test')],
        ['label' => __('words_test.difficulties.medium'), 'difficulty' => 'medium', 'href' => route('words.test.medium')],
        ['label' => __('words_test.difficulties.hard'), 'difficulty' => 'hard', 'href' => route('words.test.hard')],
    ];

    $heroDescription = __('words_test.hero.' . $difficulty);
  @endphp

  <script>
    window.wordsTestI18n = @json(trans('words_test'));
  </script>

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
        <p class="text-sm text-muted-foreground">{{ __('words_test.practice_line', ['lang' => strtoupper($siteLocale ?? app()->getLocale())]) }}</p>
        <h1 class="text-3xl font-semibold text-foreground">{{ __('words_test.title') }}</h1>
        <p class="text-muted-foreground max-w-2xl">{{ $heroDescription }}</p>
      </div>
      <div class="flex flex-wrap gap-3">
        <button id="reset-btn" class="inline-flex items-center gap-2 rounded-xl border border-border bg-background px-4 py-2 text-sm font-semibold text-foreground shadow-sm transition hover:-translate-y-0.5 hover:shadow">
          <span>{{ __('words_test.buttons.reset') }}</span>
        </button>
      </div>
    </div>

    <div class="rounded-2xl bg-card p-4 shadow-soft border border-border/70 space-y-2">
      <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div class="space-y-1">
          <p class="text-sm font-semibold text-foreground">{{ __('words_test.labels.study_language') }}</p>
          <p id="study-language-helper" class="text-sm text-muted-foreground">{{ __('words_test.labels.study_language_helper') }}</p>
        </div>
        <div class="flex items-center gap-3">
          <label class="sr-only" for="study-language-select">{{ __('words_test.labels.study_language') }}</label>
          <select
            id="study-language-select"
            class="w-48 rounded-xl border border-border/80 bg-background px-3 py-2 text-sm font-semibold text-foreground shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary"
          >
            <option value="">{{ __('words_test.labels.choose_language') }}</option>
            @foreach (['uk', 'pl', 'en'] as $lang)
              <option value="{{ $lang }}" {{ ($studyLang ?? '') === $lang ? 'selected' : '' }}>{{ __('words_test.languages.' . $lang) }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div id="study-language-warning" class="hidden rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800"></div>
    </div>

    <div class="grid gap-4 md:grid-cols-[1.6fr_1fr]">
      <div class="rounded-2xl bg-card p-6 shadow-soft border border-border/70" id="question-card">
        <div class="mb-4 flex flex-wrap items-center gap-2" role="tablist" aria-label="{{ __('words_test.labels.difficulty_tabs') }}">
          @foreach ($tabs as $tab)
            <a
              href="{{ $tab['href'] }}"
              role="tab"
              aria-selected="{{ $tab['difficulty'] === $difficulty ? 'true' : 'false' }}"
              class="px-4 py-2 rounded-full transition {{ $tab['difficulty'] === $difficulty ? 'bg-primary text-primary-foreground shadow-sm' : 'bg-muted text-muted-foreground hover:bg-muted/70' }}"
            >
              {{ $tab['label'] }}
            </a>
          @endforeach
        </div>
        <div class="flex items-center justify-between gap-3">
          <div class="inline-flex items-center gap-2 rounded-full bg-primary/10 px-3 py-1 text-sm font-medium text-primary">
            <span class="h-2 w-2 rounded-full bg-primary"></span>
            <span id="question-label">{{ __('words_test.labels.loading_question') }}</span>
          </div>
          <div class="text-sm text-muted-foreground" id="queue-counter"></div>
        </div>

        <div class="mt-6 space-y-6" id="question-wrapper">
          <div class="space-y-2">
            <p class="text-sm uppercase tracking-[0.08em] text-muted-foreground">{{ __('words_test.labels.translation') }}</p>
            <div id="question-prompt" class="text-3xl font-semibold text-foreground">{{ __('words_test.labels.waiting') }}</div>
            <p id="question-tags" class="text-sm text-muted-foreground"></p>
          </div>

          <div class="grid gap-3 md:grid-cols-2" id="options"></div>

          <div id="input-wrapper" class="hidden space-y-3">
            <form id="answer-form" class="space-y-3">
              <div class="space-y-2">
                <p class="text-sm text-muted-foreground">{{ __('words_test.labels.enter_answer') }}</p>
                <div class="relative">
                  <input
                    id="answer-input"
                    type="text"
                    class="w-full rounded-xl border border-border/80 bg-muted px-4 py-3 text-lg font-semibold text-foreground shadow-sm transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary"
                    placeholder="{{ __('words_test.placeholders.enter_word_en') }}"
                  >
                  <ul id="suggestions" class="absolute left-0 top-full z-10 mt-2 hidden max-h-60 w-full overflow-auto rounded-xl border border-border/80 bg-card shadow-lg"></ul>
                </div>
              </div>
              <div class="flex flex-wrap items-center gap-3">
                <button id="submit-answer" type="submit" class="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-primary-foreground shadow transition hover:-translate-y-0.5 hover:shadow">
                  {{ __('words_test.buttons.check') }}
                </button>
              </div>
            </form>
          </div>
        </div>

        <div id="empty-state" class="hidden text-muted-foreground">
          <p class="text-lg font-semibold text-foreground">{{ __('words_test.states.no_questions_title') }}</p>
          <p id="empty-state-description">{{ __('words_test.states.no_questions_body') }}</p>
        </div>
      </div>

      <div class="space-y-4">
        <div class="rounded-2xl bg-card p-5 shadow-soft border border-border/70">
          <div class="flex items-center justify-between">
            <p class="text-sm font-semibold text-muted-foreground">{{ __('words_test.states.progress') }}</p>
            <span class="rounded-full bg-muted px-3 py-1 text-xs font-semibold text-muted-foreground" id="percentage">0%</span>
          </div>
          <div class="mt-3 h-3 rounded-full bg-muted">
            <div id="progress-bar" class="h-3 rounded-full bg-primary transition-all duration-500" style="width: 0%"></div>
          </div>
          <dl class="mt-4 grid grid-cols-3 gap-3 text-sm">
            <div class="rounded-xl bg-muted px-3 py-2">
              <dt class="text-muted-foreground">{{ __('words_test.states.total') }}</dt>
              <dd id="stat-total" class="text-lg font-semibold text-foreground">0</dd>
            </div>
            <div class="rounded-xl bg-success/10 px-3 py-2 text-success">
              <dt class="text-sm">{{ __('words_test.states.correct') }}</dt>
              <dd id="stat-correct" class="text-lg font-semibold">0</dd>
            </div>
            <div class="rounded-xl bg-destructive/10 px-3 py-2 text-destructive">
              <dt class="text-sm">{{ __('words_test.states.wrong') }}</dt>
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
              <p class="text-sm font-semibold text-muted-foreground">{{ __('words_test.states.test_completed') }}</p>
              <p class="text-lg font-semibold text-foreground">{{ __('words_test.states.all_words_done') }}</p>
            </div>
          </div>
          <p class="mt-3 text-muted-foreground">{{ __('words_test.states.completed_hint') }}</p>
        </div>
      </div>
    </div>

    <div id="failure-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
      <div class="modal-backdrop absolute inset-0"></div>
      <div id="failure-card" class="relative mx-4 w-full max-w-xl rounded-2xl border border-destructive/30 bg-card p-6 shadow-2xl animate-pop">
        <div class="flex items-start gap-4">
          <div class="flex h-12 w-12 items-center justify-center rounded-full bg-destructive/10 text-destructive">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4m0 4h.01M4.93 4.93l14.14 14.14"/></svg>
          </div>
          <div class="space-y-2">
            <div>
              <p class="text-sm font-semibold text-muted-foreground">{{ __('words_test.states.test_failed') }}</p>
              <p class="text-2xl font-semibold text-foreground">{{ __('words_test.states.limit_exceeded') }}</p>
            </div>
            <p class="text-muted-foreground">{{ __('words_test.states.failure_body') }}</p>
            <div class="flex flex-wrap gap-3 pt-2">
              <button id="retry-btn" class="inline-flex items-center gap-2 rounded-xl border border-destructive/50 bg-destructive/10 px-4 py-2 text-sm font-semibold text-destructive shadow-sm transition hover:-translate-y-0.5 hover:shadow">
                {{ __('words_test.buttons.retry') }}
              </button>
              <button id="close-failure" class="inline-flex items-center gap-2 rounded-xl border border-border bg-background px-4 py-2 text-sm font-semibold text-foreground shadow-sm transition hover:-translate-y-0.5 hover:shadow">
                {{ __('words_test.buttons.close') }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const difficulty = "{{ $difficulty }}";
      const isEasy = difficulty === 'easy';
      const isMedium = difficulty === 'medium';

      const stateUrl = "{{ $stateUrl }}";
      const checkUrl = "{{ $checkUrl }}";
      const resetUrl = "{{ $resetUrl }}";
      const setStudyLangUrl = "{{ $setStudyLangUrl }}";
      const csrfToken = '{{ csrf_token() }}';

      const i18n = window.wordsTestI18n || {};

      let studyLang = @json($studyLang);
      let siteLocale = @json($siteLocale);

      const questionLabel = document.getElementById('question-label');
      const queueCounter = document.getElementById('queue-counter');
      const questionPrompt = document.getElementById('question-prompt');
      const questionTags = document.getElementById('question-tags');
      const optionsWrapper = document.getElementById('options');
      const inputWrapper = document.getElementById('input-wrapper');
      const answerInput = document.getElementById('answer-input');
      const answerForm = document.getElementById('answer-form');
      const submitAnswerBtn = document.getElementById('submit-answer');
      const suggestionsList = document.getElementById('suggestions');
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
      const emptyStateDescription = document.getElementById('empty-state-description');
      const resetBtn = document.getElementById('reset-btn');
      const retryBtn = document.getElementById('retry-btn');
      const closeFailure = document.getElementById('close-failure');
      const studyLanguageSelect = document.getElementById('study-language-select');
      const studyLanguageWarning = document.getElementById('study-language-warning');
      const studyLanguageHelper = document.getElementById('study-language-helper');

      let currentQuestion = null;
      let loading = false;
      let suggestionTimeout = null;

      function t(path, replacements = {}) {
        const value = path.split('.').reduce((acc, key) => (acc && acc[key] !== undefined ? acc[key] : null), i18n);
        if (typeof value !== 'string') return path;

        return Object.entries(replacements).reduce((text, [key, val]) => text.replace(new RegExp(`:${key}`, 'g'), val), value);
      }

      function languageName(code) {
        return t(`languages.${code}`);
      }

      function animate(el, className = 'animate-soft') {
        if (!el) return;
        el.classList.remove(className);
        void el.offsetWidth;
        el.classList.add(className);
      }

      async function fetchState() {
        loading = true;
        toggleAnswerControls(false);
        const response = await fetch(stateUrl, { headers: { Accept: 'application/json' } });
        const data = await response.json();
        loading = false;
        updateState(data);
      }

      function updateStudyLanguageUI(needsSelection) {
        if (!studyLanguageSelect) return;
        if (studyLang) {
          studyLanguageSelect.value = studyLang;
        } else {
          studyLanguageSelect.value = '';
        }

        const needsNonEn = siteLocale === 'en';
        const message = needsNonEn ? t('alerts.needs_non_en') : t('alerts.needs_language');

        if (needsSelection) {
          studyLanguageWarning.textContent = message;
          studyLanguageWarning.classList.remove('hidden');
          questionWrapper.classList.add('hidden');
          emptyState.classList.remove('hidden');
          emptyStateDescription.textContent = message;
          questionLabel.textContent = t('states.choose_language');
          queueCounter.textContent = '';
          toggleAnswerControls(false);
        } else {
          studyLanguageWarning.classList.add('hidden');
          if (!loading && currentQuestion) {
            toggleAnswerControls(true);
          }
        }

        if (needsNonEn) {
          studyLanguageHelper.textContent = t('labels.study_language_helper_en');
        } else {
          studyLanguageHelper.textContent = t('labels.study_language_helper');
        }
      }

      function updateState(data) {
        studyLang = data.studyLang ?? studyLang;
        siteLocale = data.siteLocale ?? siteLocale;

        const needsSelection = data.needsStudyLanguage;
        updateStudyLanguageUI(needsSelection);

        if (needsSelection) {
          renderStats(data.stats, data.percentage, data.totalCount ?? 0);
          completion.hidden = true;
          feedback.hidden = true;
          return;
        }

        renderStats(data.stats, data.percentage, data.totalCount);
        completion.hidden = !data.completed;
        toggleFailureModal(data.failed);

        if (data.failed) {
          currentQuestion = null;
          questionWrapper.classList.add('hidden');
          emptyState.classList.add('hidden');
          questionLabel.textContent = t('states.test_failed');
          queueCounter.textContent = '';
          toggleAnswerControls(false);
          return;
        }

        if (!data.question) {
          currentQuestion = null;
          questionWrapper.classList.add('hidden');
          emptyState.classList.remove('hidden');
          queueCounter.textContent = '';
          questionLabel.textContent = data.completed ? t('states.all_done_short') : t('states.no_questions');
          toggleAnswerControls(false);
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
        const targetLanguage = studyLang === 'en' ? languageName(siteLocale) : languageName(studyLang || '');
        const label = isEasy
          ? (question.questionType === 'en_to_lang' ? t('labels.select_translation', { lang: targetLanguage }) : t('labels.select_english'))
          : (studyLang === 'en' ? t('labels.type_english_word') : t('labels.type_translation', { lang: targetLanguage }));
        questionLabel.textContent = label;

        queueCounter.textContent = t('labels.question_counter', { current: totalAnswered + 1, total: totalCount });
        questionPrompt.textContent = question.prompt;
        questionTags.textContent = (question.tags || []).join(', ');

        optionsWrapper.classList.toggle('hidden', !isEasy);
        inputWrapper.classList.toggle('hidden', isEasy);

        if (isEasy) {
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
        } else {
          optionsWrapper.innerHTML = '';
          answerInput.value = '';
          hideSuggestions();
          const placeholder = studyLang === 'en'
            ? t('placeholders.enter_word_en')
            : t('placeholders.enter_translation', { lang: targetLanguage });
          answerInput.placeholder = placeholder;
          setTimeout(() => answerInput?.focus(), 50);
        }

        toggleAnswerControls(!loading);
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

      function toggleAnswerControls(enabled) {
        if (isEasy) {
          toggleOptions(enabled);
          return;
        }

        if (answerInput) {
          answerInput.disabled = !enabled;
          answerInput.classList.toggle('opacity-60', !enabled);
        }

        if (submitAnswerBtn) {
          submitAnswerBtn.disabled = !enabled;
          submitAnswerBtn.classList.toggle('opacity-60', !enabled);
        }
      }

      async function submitAnswer(answer, selectedButton) {
        if (!currentQuestion || loading) return;

        const value = typeof answer === 'string' ? answer : (answerInput?.value ?? '');
        const preparedAnswer = value.trim();
        if (!preparedAnswer) {
          return;
        }

        loading = true;
        toggleAnswerControls(false);
        hideSuggestions();

        const formData = new FormData();
        formData.append('word_id', currentQuestion.word_id);
        formData.append('answer', preparedAnswer);

        const response = await fetch(checkUrl, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            Accept: 'application/json',
          },
          body: formData,
        });

        if (!response.ok) {
          loading = false;
          toggleAnswerControls(true);
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
            questionLabel.textContent = t('states.test_failed');
            queueCounter.textContent = '';
            toggleAnswerControls(false);
            return;
          }

          if (data.completed || !data.question) {
            completion.hidden = false;
            questionWrapper.classList.add('hidden');
            emptyState.classList.remove('hidden');
            questionLabel.textContent = t('states.all_done_short');
            queueCounter.textContent = '';
            toggleAnswerControls(false);
          } else {
            renderQuestion(data.question, data.totalCount, data.stats.total);
          }
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
        feedbackChip.textContent = isCorrect ? t('feedback.correct') : t('feedback.wrong');
        feedbackChip.className = `inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-semibold ${isCorrect ? 'bg-success/10 text-success' : 'bg-destructive/10 text-destructive'}`;

        if (result.questionType === 'en_to_lang') {
          feedbackTitle.textContent = `${result.word} → ${result.correctAnswer}`;
        } else {
          feedbackTitle.textContent = `${result.translation} → ${result.correctAnswer}`;
        }

        feedbackBody.textContent = isCorrect ? t('feedback.good_job') : t('feedback.try_again');
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
            Accept: 'application/json',
          },
        });

        const data = await response.json();
        hideSuggestions();
        updateState(data);
        if (button) {
          button.disabled = false;
          button.classList.remove('opacity-60');
        }
      }

      function hideSuggestions() {
        if (!suggestionsList) return;
        suggestionsList.innerHTML = '';
        suggestionsList.classList.add('hidden');
      }

      function renderSuggestions(query) {
        if (!isMedium || !suggestionsList) return;
        if (suggestionTimeout) {
          clearTimeout(suggestionTimeout);
        }

        const term = query.trim();
        if (!term) {
          hideSuggestions();
          return;
        }

        suggestionTimeout = setTimeout(async () => {
          try {
            const response = await fetch(`/api/search?lang=en&q=${encodeURIComponent(term)}`);
            if (!response.ok) {
              hideSuggestions();
              return;
            }
            const data = await response.json();
            if (answerInput.value.trim() !== term) {
              return;
            }
            suggestionsList.innerHTML = '';
            if (!Array.isArray(data) || data.length === 0) {
              hideSuggestions();
              return;
            }

            data.forEach((item) => {
              const value = item.en ?? item.word ?? item;
              if (!value) return;
              const li = document.createElement('li');
              li.textContent = value;
              li.className = 'cursor-pointer px-4 py-2 text-sm hover:bg-muted';
              li.addEventListener('click', () => {
                answerInput.value = value;
                hideSuggestions();
                answerInput.focus();
              });
              suggestionsList.appendChild(li);
            });

            if (suggestionsList.childElementCount > 0) {
              suggestionsList.classList.remove('hidden');
            } else {
              hideSuggestions();
            }
          } catch (e) {
            hideSuggestions();
          }
        }, 200);
      }

      async function updateStudyLanguage(lang) {
        if (!lang) return;
        try {
          const response = await fetch(setStudyLangUrl, {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': csrfToken,
              'Content-Type': 'application/json',
              Accept: 'application/json',
            },
            body: JSON.stringify({ lang, difficulty }),
          });

          if (!response.ok) return;

          const data = await response.json();
          studyLang = data.study_lang;
          await fetchState();
        } catch (e) {
          // ignore
        }
      }

      if (answerForm && !isEasy) {
        answerForm.addEventListener('submit', (event) => {
          event.preventDefault();
          submitAnswer();
        });
      }

      if (answerInput && !isEasy) {
        answerInput.addEventListener('input', (event) => {
          if (isMedium) {
            renderSuggestions(event.target.value);
          }
        });

        answerInput.addEventListener('focus', () => {
          if (isMedium && suggestionsList && suggestionsList.childElementCount > 0) {
            suggestionsList.classList.remove('hidden');
          }
        });
      }

      document.addEventListener('click', (event) => {
        if (suggestionsList && !suggestionsList.contains(event.target) && event.target !== answerInput) {
          hideSuggestions();
        }
      });

      if (studyLanguageSelect) {
        studyLanguageSelect.addEventListener('change', (event) => {
          const value = event.target.value;
          updateStudyLanguage(value);
        });
      }

      resetBtn.addEventListener('click', () => resetTest(resetBtn));
      retryBtn.addEventListener('click', () => resetTest(retryBtn));
      closeFailure.addEventListener('click', () => toggleFailureModal(false));

      fetchState();
    });
  </script>
@endsection
