@extends('layouts.engram')

@section('title', __('words_test.title'))

@section('content')
  @php
    $difficulty = $difficulty ?? 'easy';
    $tabs = [
        ['label' => 'Easy', 'difficulty' => 'easy', 'href' => route('words.test')],
        ['label' => 'Medium', 'difficulty' => 'medium', 'href' => route('words.test.medium')],
        ['label' => 'Hard', 'difficulty' => 'hard', 'href' => route('words.test.hard')],
    ];

    $heroDescriptionKey = [
        'easy' => 'words_test.description_easy',
        'medium' => 'words_test.description_medium',
        'hard' => 'words_test.description_hard',
    ][$difficulty] ?? 'words_test.description_easy';
    $heroDescription = __($heroDescriptionKey);

    // $studyLangOptions is now passed from controller based on LanguageManager
  @endphp

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
      .animate-fade { animation: fade-in-soft 240ms ease; }
      .animate-bounce { animation: pop-in 240ms ease; }

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
    <div class="rounded-3xl border border-[var(--border)] bg-gradient-to-br from-brand-50/80 via-white to-white p-6 shadow-card">
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="space-y-2">
          <span class="inline-flex items-center gap-2 rounded-full bg-brand-100 px-3 py-1 text-xs font-semibold text-brand-700">{{ __('public.nav.words_test') }}</span>
          <p class="text-sm text-muted-foreground">{{ __('words_test.subtitle') }} · {{ __('words_test.active_lang') }}: <span class="font-semibold text-brand-700">{{ strtoupper($siteLocale) }}</span></p>
          <h1 class="text-3xl font-bold text-foreground">{{ __('words_test.quick_test') }}</h1>
          <p class="text-muted-foreground max-w-2xl">{{ $heroDescription }}</p>
        </div>
        <div class="flex flex-wrap gap-3">
          <button id="reset-btn" class="inline-flex items-center gap-2 rounded-full border border-brand-200 bg-white px-4 py-2 text-sm font-semibold text-brand-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow">
            <span>{{ __('words_test.restart') }}</span>
          </button>
        </div>
      </div>
    </div>

    <div class="flex flex-col gap-4 md:grid md:grid-cols-[1.6fr_1fr]">
      <div class="rounded-2xl bg-[var(--card)] p-6 shadow-card border border-[var(--border)] order-2 md:order-1" id="question-card">
        <div class="mb-4 flex flex-wrap items-center gap-2" role="tablist" aria-label="{{ __('words_test.difficulty_modes') }}">
          @foreach ($tabs as $tab)
            <a
              href="{{ $tab['href'] }}"
              role="tab"
              aria-selected="{{ $tab['difficulty'] === $difficulty ? 'true' : 'false' }}"
              class="px-4 py-2 rounded-full transition {{ $tab['difficulty'] === $difficulty ? 'bg-brand-600 text-white shadow-card' : 'bg-muted text-muted-foreground hover:bg-muted/70' }}"
            >
              {{ $tab['label'] }}
            </a>
          @endforeach
        </div>

        <!-- Study Language Selector -->
        <div class="mb-4 p-4 rounded-xl bg-muted/50 border border-border/50" id="study-lang-selector">
          <div class="flex flex-wrap items-center gap-3">
            <label for="study-lang" class="text-sm font-semibold text-muted-foreground">{{ __('words_test.study_lang') }}:</label>
            @if ($singleStudyLangName)
              <span class="text-sm font-semibold text-foreground">{{ $singleStudyLangName }}</span>
            @elseif (count($studyLangOptions) > 1)
              <select id="study-lang" class="rounded-lg border border-border bg-background px-3 py-2 text-sm font-medium text-foreground shadow-sm transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                @foreach ($studyLangOptions as $langCode => $langName)
                  <option value="{{ $langCode }}" {{ $studyLang === $langCode ? 'selected' : '' }}>{{ $langName }}</option>
                @endforeach
              </select>
              <p class="text-xs text-muted-foreground">{{ __('words_test.study_lang_hint') }}</p>
            @endif
          </div>
          <!-- Warning when site locale is English and no study language selected -->
          <div id="study-lang-warning" class="mt-3 p-3 rounded-lg bg-warning/10 border border-warning/30 text-warning text-sm {{ (!$studyLang && $siteLocale === 'en' && count($studyLangOptions) > 1) ? '' : 'hidden' }}">
            <span class="font-semibold">⚠️</span> {{ __('words_test.select_study_lang_warning') }}
          </div>
          <!-- Info when no languages available -->
          @if(empty($studyLangOptions))
          <div class="mt-3 p-3 rounded-lg bg-destructive/10 border border-destructive/30 text-destructive text-sm">
            <span class="font-semibold">⚠️</span> {{ __('words_test.no_langs_available') }}
          </div>
          @endif
        </div>

        <div class="flex items-center justify_between gap-3">
          <div class="inline-flex items-center gap-2 rounded-full bg-brand-100 px-3 py-1 text-sm font-semibold text-brand-700 shadow-sm">
            <span class="h-2 w-2 rounded-full bg-brand-500"></span>
            <span id="question-label">{{ __('words_test.loading') }}</span>
          </div>
          <div class="text-sm text-muted-foreground" id="queue-counter"></div>
        </div>

        <div class="mt-6 space-y-6" id="question-wrapper">
          <div class="space-y-2">
            <p class="text-sm uppercase tracking-[0.08em] text-muted-foreground">{{ __('words_test.translation') }}</p>
            <div id="question-prompt" class="text-3xl font-semibold text-foreground">{{ __('words_test.wait') }}</div>
            <p id="question-tags" class="text-sm text-muted-foreground"></p>
          </div>

          <div class="grid gap-3 md:grid-cols-2" id="options"></div>

          <div id="input-wrapper" class="hidden space-y-3">
            <form id="answer-form" class="space-y-3">
              <div class="space-y-2">
                <label for="answer-input" class="text-sm font-semibold text-muted-foreground">{{ __('words_test.your_answer') }}</label>
                <div class="relative">
                  <input
                    id="answer-input"
                    type="text"
                    name="answer"
                    autocomplete="off"
                    class="w-full rounded-xl border border-border/80 bg-muted px-4 py-3 text-lg font-semibold text-foreground shadow-sm transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary"
                    placeholder="{{ __('words_test.enter_word') }}"
                  >
                  <ul id="suggestions" class="absolute left-0 top-full z-10 mt-2 hidden max-h-60 w-full overflow-auto rounded-xl border border-border/80 bg-card shadow-lg"></ul>
                </div>
              </div>
              <div class="flex flex-wrap items-center gap-3">
                <button id="submit-answer" type="submit" class="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-primary-foreground shadow transition hover:-translate-y-0.5 hover:shadow">
                  {{ __('words_test.check') }}
                </button>
              </div>
            </form>
          </div>
        </div>

        <div id="empty-state" class="hidden text-muted-foreground">
          <p class="text-lg font-semibold text-foreground">{{ __('words_test.no_questions') }}</p>
          <p>{{ __('words_test.no_questions_msg') }}</p>
        </div>
      </div>

      <div class="space-y-4 order-1 md:order-2 md:static sticky top-2 z-20">
        <div class="rounded-2xl bg-card p-5 shadow-soft border border-border/70">
          <div class="flex items-center justify-between">
            <p class="text-sm font-semibold text-muted-foreground">{{ __('words_test.progress') }}</p>
            <span class="rounded-full bg-muted px-3 py-1 text-xs font-semibold text-muted-foreground" id="percentage">0%</span>
          </div>
          <div class="mt-3 h-3 rounded-full bg-muted">
            <div id="progress-bar" class="h-3 rounded-full bg-brand-600 transition-all duration-500" style="width: 0%"></div>
          </div>
          <dl class="mt-4 grid grid-cols-3 gap-3 text-sm">
            <div class="rounded-xl bg-muted px-3 py-2">
              <dt class="text-muted-foreground">{{ __('words_test.total') }}</dt>
              <dd id="stat-total" class="text-lg font-semibold text-foreground">0</dd>
            </div>
            <div class="rounded-xl bg-success/10 px-3 py-2 text-success">
              <dt class="text-sm">{{ __('words_test.correct') }}</dt>
              <dd id="stat-correct" class="text-lg font-semibold">0</dd>
            </div>
            <div class="rounded-xl bg-destructive/10 px-3 py-2 text-destructive">
              <dt class="text-sm">{{ __('words_test.errors') }}</dt>
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
              <p class="text-sm font-semibold text-muted-foreground">{{ __('words_test.test_completed') }}</p>
              <p class="text-lg font-semibold text-foreground">{{ __('words_test.all_words_done') }}</p>
            </div>
          </div>
          <p class="mt-3 text-muted-foreground">{{ __('words_test.can_restart') }}</p>
        </div>
      </div>
    </div>

    <div id="failure-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
      <div class="modal-backdrop absolute inset-0 animate-fade"></div>
      <div id="failure-card" class="relative mx-4 w-full max-w-xl rounded-2xl border border-destructive/30 bg-card p-6 shadow-2xl animate-bounce">
        <div class="flex items-start gap-4">
          <div class="flex h-12 w-12 items-center justify-center rounded-full bg-destructive/10 text-destructive">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4m0 4h.01M4.93 4.93l14.14 14.14"/></svg>
          </div>
          <div class="space-y-2">
            <div>
              <p class="text-sm font-semibold text-muted-foreground">{{ __('words_test.test_failed') }}</p>
              <p class="text-2xl font-semibold text-foreground">{{ __('words_test.error_limit') }}</p>
            </div>
            <p class="text-muted-foreground">{{ __('words_test.error_limit_msg') }}</p>
            <div class="flex flex-wrap gap-3 pt-2">
              <button id="retry-btn" class="inline-flex items-center gap-2 rounded-xl border border-destructive/50 bg-destructive/10 px-4 py-2 text-sm font-semibold text-destructive shadow-sm transition hover:-translate-y-0.5 hover:shadow">
                {{ __('words_test.retry') }}
              </button>
              <button id="close-failure" class="inline-flex items-center gap-2 rounded-xl border border-border bg-background px-4 py-2 text-sm font-semibold text-foreground shadow-sm transition hover:-translate-y-0.5 hover:shadow">
                {{ __('words_test.close') }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Localization strings for JavaScript
    window.wordsTestI18n = @json(__('words_test'));

    document.addEventListener('DOMContentLoaded', () => {
      const i18n = window.wordsTestI18n;
      const difficulty = "{{ $difficulty }}";
      const isEasy = difficulty === 'easy';
      const isMedium = difficulty === 'medium';

      const stateUrl = "{{ $stateUrl }}";
      const checkUrl = "{{ $checkUrl }}";
      const resetUrl = "{{ $resetUrl }}";
      const setStudyLangUrl = "{{ $setStudyLangUrl }}";
      const csrfToken = '{{ csrf_token() }}';

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
      const resetBtn = document.getElementById('reset-btn');
      const retryBtn = document.getElementById('retry-btn');
      const closeFailure = document.getElementById('close-failure');
      const studyLangSelect = document.getElementById('study-lang');
      const studyLangWarning = document.getElementById('study-lang-warning');

      let currentQuestion = null;
      let loading = false;
      let suggestionTimeout = null;
      let currentStudyLang = "{{ $studyLang ?? '' }}";
      let currentSiteLocale = "{{ $siteLocale }}";
      let availableStudyLangs = @json($availableStudyLangs ?? []);

      function animate(el, className = 'animate-soft') {
        if (!el) return;
        el.classList.remove(className);
        void el.offsetWidth;
        el.classList.add(className);
      }

      function updateStudyLangUI(studyLang, siteLocale, showWarning = false) {
        currentStudyLang = studyLang;
        currentSiteLocale = siteLocale;

        // Update select value
        if (studyLangSelect && studyLang) {
          studyLangSelect.value = studyLang;
        }

        // Show/hide warning based on current state computed by caller
        if (studyLangWarning) {
          studyLangWarning.classList.toggle('hidden', !showWarning);
        }
      }

      async function setStudyLanguage(lang) {
        loading = true;
        toggleAnswerControls(false);

        try {
          const formData = new FormData();
          formData.append('lang', lang);
          formData.append('difficulty', difficulty);

          const response = await fetch(setStudyLangUrl, {
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
          if (data.ok) {
            updateStudyLangUI(data.studyLang, data.siteLocale, false);
            // Fetch new state after language change
            await fetchState();
          }
        } catch (e) {
          console.error('Error setting study language:', e);
        }

        loading = false;
      }

      async function fetchState() {
        loading = true;
        toggleAnswerControls(false);
        const response = await fetch(stateUrl, { headers: { Accept: 'application/json' } });
        const data = await response.json();
        loading = false;
        updateState(data);
      }

      function updateState(data) {
        availableStudyLangs = Array.isArray(data.availableStudyLangs) ? data.availableStudyLangs : [];
        const shouldShowWarning = data.needsStudyLanguage && data.siteLocale === 'en' && availableStudyLangs.length >= 2;
        // Update study language UI
        updateStudyLangUI(data.studyLang, data.siteLocale, shouldShowWarning);

        // If needs study language selection, show warning and disable test
        if (data.needsStudyLanguage) {
          currentQuestion = null;
          questionWrapper.classList.add('hidden');
          emptyState.classList.remove('hidden');
          questionLabel.textContent = i18n.select_study_lang;
          queueCounter.textContent = '';
          toggleAnswerControls(false);
          renderStats({ correct: 0, wrong: 0, total: 0 }, 0, 0);
          return;
        }

        renderStats(data.stats, data.percentage, data.totalCount);
        completion.hidden = !data.completed;
        toggleFailureModal(data.failed);

        if (data.failed) {
          currentQuestion = null;
          questionWrapper.classList.add('hidden');
          emptyState.classList.add('hidden');
          questionLabel.textContent = i18n.test_failed;
          queueCounter.textContent = '';
          toggleAnswerControls(false);
          return;
        }

        if (!data.question) {
          currentQuestion = null;
          questionWrapper.classList.add('hidden');
          emptyState.classList.remove('hidden');
          queueCounter.textContent = '';
          questionLabel.textContent = data.completed ? i18n.all_done : i18n.no_question;
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
        // Determine label based on question type
        let label;
        if (isEasy) {
          label = i18n.choose_translation;
        } else {
          label = i18n.enter_word;
        }
        questionLabel.textContent = label;

        // Use translation string for question counter
        const questionOfText = i18n.question_of
          .replace(':current', totalAnswered + 1)
          .replace(':total', totalCount);
        queueCounter.textContent = questionOfText;
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
          answerInput.placeholder = i18n.enter_word;
          hideSuggestions();
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
            questionLabel.textContent = i18n.test_failed;
            queueCounter.textContent = '';
            toggleAnswerControls(false);
            return;
          }

          if (data.completed || !data.question) {
            completion.hidden = false;
            questionWrapper.classList.add('hidden');
            emptyState.classList.remove('hidden');
            questionLabel.textContent = i18n.all_done;
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
          failureModal.classList.add('flex', 'items-center', 'justify-center');
          failureCard.classList.remove('animate-bounce');
          void failureCard.offsetWidth;
          failureCard.classList.add('animate-bounce');
          const backdrop = failureModal.querySelector('.animate-fade');
          if (backdrop) {
            backdrop.classList.remove('animate-fade');
            void backdrop.offsetWidth;
            backdrop.classList.add('animate-fade');
          }
        } else {
          failureModal.classList.remove('flex', 'items-center', 'justify-center');
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
        feedbackChip.textContent = isCorrect ? i18n.feedback_correct : i18n.feedback_error;
        feedbackChip.className = `inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-semibold ${isCorrect ? 'bg-success/10 text-success' : 'bg-destructive/10 text-destructive'}`;

        if (result.questionType === 'en_to_translation') {
          feedbackTitle.textContent = `${result.word} → ${result.correctAnswer}`;
        } else {
          feedbackTitle.textContent = `${result.translation} → ${result.correctAnswer}`;
        }

        feedbackBody.textContent = isCorrect
          ? i18n.feedback_correct_msg
          : i18n.feedback_error_msg;
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

      // Study language change handler
      if (studyLangSelect) {
        studyLangSelect.addEventListener('change', (event) => {
          const newLang = event.target.value;
          if (newLang && newLang !== currentStudyLang) {
            setStudyLanguage(newLang);
          }
        });
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

      resetBtn.addEventListener('click', () => resetTest(resetBtn));
      retryBtn.addEventListener('click', () => resetTest(retryBtn));
      closeFailure.addEventListener('click', () => toggleFailureModal(false));

      fetchState();
    });
  </script>
@endsection
