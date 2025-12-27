@extends('layouts.engram')

@section('title', __('verbs_test.title'))

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
  </style>

  <!-- Header -->
  <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <div class="space-y-1">
      <p class="text-sm text-muted-foreground">{{ __('verbs_test.subtitle') }}</p>
      <h1 class="text-3xl font-semibold text-foreground">{{ __('verbs_test.title') }}</h1>
      <p class="text-muted-foreground max-w-2xl">{{ __('verbs_test.description') }}</p>
    </div>
  </div>

  <div class="grid gap-4 md:grid-cols-[1.6fr_1fr]">
    <!-- Main Test Card -->
    <div class="rounded-2xl bg-card p-6 shadow-soft border border-border/70" id="test-card">
      <!-- Settings Panel -->
      <div id="settings-panel" class="space-y-4 mb-6 p-4 rounded-xl bg-muted/50 border border-border/50">
        <h2 class="text-lg font-semibold text-foreground">{{ __('verbs_test.settings') }}</h2>
        
        <div class="grid gap-4 sm:grid-cols-2">
          <!-- Mode Selection -->
          <div class="space-y-2">
            <label class="text-sm font-semibold text-muted-foreground">{{ __('verbs_test.mode') }}</label>
            <div class="flex gap-2">
              <button type="button" id="mode-typing" class="flex-1 px-3 py-2 text-sm font-medium rounded-lg border border-primary bg-primary/10 text-primary transition hover:bg-primary/20">
                {{ __('verbs_test.mode_typing') }}
              </button>
              <button type="button" id="mode-choice" class="flex-1 px-3 py-2 text-sm font-medium rounded-lg border border-border bg-background text-muted-foreground transition hover:bg-muted">
                {{ __('verbs_test.mode_choice') }}
              </button>
            </div>
          </div>

          <!-- Ask What Selection -->
          <div class="space-y-2">
            <label for="askWhat" class="text-sm font-semibold text-muted-foreground">{{ __('verbs_test.ask_what') }}</label>
            <select id="askWhat" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm font-medium text-foreground shadow-sm transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
              <option value="random">{{ __('verbs_test.ask_random') }}</option>
              <option value="f1">{{ __('verbs_test.ask_f1') }}</option>
              <option value="f2">{{ __('verbs_test.ask_f2') }}</option>
              <option value="f3">{{ __('verbs_test.ask_f3') }}</option>
              <option value="f4">{{ __('verbs_test.ask_f4') }}</option>
            </select>
          </div>

          <!-- Question Count -->
          <div class="space-y-2">
            <label for="count" class="text-sm font-semibold text-muted-foreground">{{ __('verbs_test.question_count') }}</label>
            <input type="number" id="count" min="5" max="100" value="20" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm font-medium text-foreground shadow-sm transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
          </div>

          <!-- Show Translation Checkbox -->
          <div class="flex items-center gap-3 pt-6">
            <input type="checkbox" id="showTranslation" checked class="h-5 w-5 rounded border-border text-primary focus:ring-primary/20">
            <label for="showTranslation" class="text-sm font-semibold text-muted-foreground">{{ __('verbs_test.show_translation') }}</label>
          </div>
        </div>

        <!-- Start Button -->
        <div class="flex flex-wrap gap-3 pt-2">
          <button type="button" id="startBtn" class="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-primary-foreground shadow transition hover:-translate-y-0.5 hover:shadow">
            {{ __('verbs_test.start') }}
          </button>
          <button type="button" id="restartBtn" class="hidden inline-flex items-center gap-2 rounded-xl border border-border bg-background px-4 py-2 text-sm font-semibold text-foreground shadow-sm transition hover:-translate-y-0.5 hover:shadow">
            {{ __('verbs_test.restart') }}
          </button>
        </div>
      </div>

      <!-- Question Area (hidden initially) -->
      <div id="question-area" class="hidden space-y-6">
        <!-- Question Header -->
        <div class="flex items-center justify-between gap-3">
          <div class="inline-flex items-center gap-2 rounded-full bg-primary/10 px-3 py-1 text-sm font-medium text-primary">
            <span class="h-2 w-2 rounded-full bg-primary"></span>
            <span id="askLabel">{{ __('verbs_test.question') }}</span>
          </div>
          <div class="text-sm text-muted-foreground" id="progressText">0 / 0</div>
        </div>

        <!-- Question Content -->
        <div class="space-y-4">
          <!-- Base Verb Display -->
          <div class="space-y-2">
            <p class="text-sm uppercase tracking-[0.08em] text-muted-foreground">{{ __('verbs_test.base_verb') }}</p>
            <div id="baseVerb" class="text-3xl font-semibold text-foreground">—</div>
          </div>

          <!-- Translation (optional) -->
          <div id="ukVerb" class="text-lg text-muted-foreground">—</div>

          <!-- Hint (all forms) -->
          <div id="hint" class="hidden p-3 rounded-lg bg-muted/50 border border-border/50 text-sm text-muted-foreground">
            <span class="font-semibold">{{ __('verbs_test.hint_label') }}:</span>
            <span id="hintText"></span>
          </div>
        </div>

        <!-- Typing Mode -->
        <div id="typingBox" class="space-y-3">
          <div class="space-y-2">
            <label for="answerInput" class="text-sm font-semibold text-muted-foreground">{{ __('verbs_test.your_answer') }}</label>
            <input type="text" id="answerInput" autocomplete="off" class="w-full rounded-xl border border-border/80 bg-muted px-4 py-3 text-lg font-semibold text-foreground shadow-sm transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary" placeholder="{{ __('verbs_test.enter_form') }}">
          </div>
        </div>

        <!-- Choice Mode -->
        <div id="choiceBox" class="hidden grid gap-3 md:grid-cols-2">
          <!-- Choices will be rendered here -->
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap items-center gap-3">
          <button type="button" id="checkBtn" class="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-primary-foreground shadow transition hover:-translate-y-0.5 hover:shadow">
            {{ __('verbs_test.check') }}
          </button>
          <button type="button" id="revealBtn" class="inline-flex items-center gap-2 rounded-xl border border-border bg-background px-4 py-2 text-sm font-semibold text-foreground shadow-sm transition hover:-translate-y-0.5 hover:shadow">
            {{ __('verbs_test.reveal') }}
          </button>
          <button type="button" id="nextBtn" class="hidden inline-flex items-center gap-2 rounded-xl bg-secondary px-5 py-3 text-sm font-semibold text-secondary-foreground shadow transition hover:-translate-y-0.5 hover:shadow">
            {{ __('verbs_test.next') }}
          </button>
        </div>
      </div>

      <!-- Completion Message (hidden initially) -->
      <div id="doneBox" class="hidden rounded-2xl bg-card p-5 border border-dashed border-success/50">
        <div class="flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-full bg-success/10 text-success">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>
          </div>
          <div>
            <p class="text-sm font-semibold text-muted-foreground">{{ __('verbs_test.test_completed') }}</p>
            <p id="doneText" class="text-lg font-semibold text-foreground">—</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Stats Panel -->
    <div class="space-y-4">
      <div class="rounded-2xl bg-card p-5 shadow-soft border border-border/70">
        <div class="flex items-center justify-between">
          <p class="text-sm font-semibold text-muted-foreground">{{ __('verbs_test.progress') }}</p>
          <span class="rounded-full bg-muted px-3 py-1 text-xs font-semibold text-muted-foreground" id="percentage">0%</span>
        </div>
        <div class="mt-3 h-3 rounded-full bg-muted">
          <div id="progressBar" class="h-3 rounded-full bg-primary transition-all duration-500" style="width: 0%"></div>
        </div>
        <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
          <div class="rounded-xl bg-success/10 px-3 py-2 text-success">
            <dt class="text-sm">{{ __('verbs_test.correct') }}</dt>
            <dd id="correct" class="text-lg font-semibold">0</dd>
          </div>
          <div class="rounded-xl bg-destructive/10 px-3 py-2 text-destructive">
            <dt class="text-sm">{{ __('verbs_test.wrong') }}</dt>
            <dd id="wrong" class="text-lg font-semibold">0</dd>
          </div>
        </dl>
      </div>

      <!-- Feedback Panel -->
      <div id="feedback" class="hidden rounded-2xl bg-card p-5 shadow-soft border border-border/70">
        <div id="feedbackChip" class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-semibold"></div>
        <div class="mt-3 space-y-2">
          <p id="feedbackTitle" class="text-xl font-semibold"></p>
          <p id="feedbackBody" class="text-muted-foreground"></p>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  // Verbs data from server
  window.__VERBS__ = @json($verbs);
  
  // Localization strings for JavaScript
  window.verbsTestI18n = @json(__('verbs_test'));

  /**
   * Irregular Verbs Test
   * Implements test logic with localStorage persistence for progress
   */
  document.addEventListener('DOMContentLoaded', () => {
    const i18n = window.verbsTestI18n || {};
    const VERBS = window.__VERBS__ || [];
    const STORAGE_KEY = 'verbs_test_state_v1';

    // DOM Elements - Settings
    const settingsPanel = document.getElementById('settings-panel');
    const startBtn = document.getElementById('startBtn');
    const restartBtn = document.getElementById('restartBtn');
    const modeTyping = document.getElementById('mode-typing');
    const modeChoice = document.getElementById('mode-choice');
    const askWhatSelect = document.getElementById('askWhat');
    const countInput = document.getElementById('count');
    const showTranslationCheckbox = document.getElementById('showTranslation');

    // DOM Elements - Question Area
    const questionArea = document.getElementById('question-area');
    const askLabel = document.getElementById('askLabel');
    const progressText = document.getElementById('progressText');
    const baseVerb = document.getElementById('baseVerb');
    const ukVerb = document.getElementById('ukVerb');
    const hint = document.getElementById('hint');
    const hintText = document.getElementById('hintText');
    const typingBox = document.getElementById('typingBox');
    const choiceBox = document.getElementById('choiceBox');
    const answerInput = document.getElementById('answerInput');
    const checkBtn = document.getElementById('checkBtn');
    const revealBtn = document.getElementById('revealBtn');
    const nextBtn = document.getElementById('nextBtn');

    // DOM Elements - Stats
    const percentage = document.getElementById('percentage');
    const progressBar = document.getElementById('progressBar');
    const correctEl = document.getElementById('correct');
    const wrongEl = document.getElementById('wrong');
    const feedback = document.getElementById('feedback');
    const feedbackChip = document.getElementById('feedbackChip');
    const feedbackTitle = document.getElementById('feedbackTitle');
    const feedbackBody = document.getElementById('feedbackBody');

    // DOM Elements - Completion
    const doneBox = document.getElementById('doneBox');
    const doneText = document.getElementById('doneText');

    // State
    let state = {
      mode: 'typing', // typing or choice
      askWhat: 'random', // random, f1, f2, f3, f4
      count: 20,
      showTranslation: true,
      queue: [], // Array of verb indices
      pos: 0,
      correct: 0,
      wrong: 0,
      currentQuestion: null,
      answered: false,
      started: false
    };

    // Form labels
    const formLabels = {
      f1: i18n.form1_label || 'Form 1 (3rd person)',
      f2: i18n.form2_label || 'Form 2 (Past)',
      f3: i18n.form3_label || 'Form 3 (Participle)',
      f4: i18n.form4_label || 'Form 4 (-ing)'
    };

    /**
     * Shuffle array in place (Fisher-Yates)
     */
    function shuffleArray(arr) {
      for (let i = arr.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [arr[i], arr[j]] = [arr[j], arr[i]];
      }
      return arr;
    }

    /**
     * Get a random form key based on askWhat setting
     */
    function getAskKey() {
      if (state.askWhat === 'random') {
        const forms = ['f1', 'f2', 'f3', 'f4'];
        return forms[Math.floor(Math.random() * forms.length)];
      }
      return state.askWhat;
    }

    /**
     * Get correct answer(s) for a verb and form
     */
    function getCorrectAnswers(verb, formKey) {
      const val = verb[formKey];
      if (Array.isArray(val)) {
        return val.map(v => v.toLowerCase().trim());
      }
      return [String(val).toLowerCase().trim()];
    }

    /**
     * Generate hint text showing all forms
     */
    function getHintText(verb) {
      const f2Str = Array.isArray(verb.f2) ? verb.f2.join(' / ') : verb.f2;
      const f3Str = Array.isArray(verb.f3) ? verb.f3.join(' / ') : verb.f3;
      return `Base: ${verb.base} • ${formLabels.f1}: ${verb.f1} • ${formLabels.f2}: ${f2Str} • ${formLabels.f3}: ${f3Str} • ${formLabels.f4}: ${verb.f4}`;
    }

    /**
     * Save state to localStorage
     */
    function saveState() {
      try {
        const stateToSave = {
          mode: state.mode,
          askWhat: state.askWhat,
          count: state.count,
          showTranslation: state.showTranslation,
          queue: state.queue,
          pos: state.pos,
          correct: state.correct,
          wrong: state.wrong,
          currentQuestion: state.currentQuestion,
          started: state.started,
          verbsHash: VERBS.length // Simple validation
        };
        localStorage.setItem(STORAGE_KEY, JSON.stringify(stateToSave));
      } catch (e) {
        console.warn('Failed to save state:', e);
      }
    }

    /**
     * Load state from localStorage
     */
    function loadState() {
      try {
        const saved = localStorage.getItem(STORAGE_KEY);
        if (!saved) return false;

        const parsed = JSON.parse(saved);
        
        // Validate state
        if (parsed.verbsHash !== VERBS.length) {
          localStorage.removeItem(STORAGE_KEY);
          return false;
        }

        // Restore state
        state.mode = parsed.mode || 'typing';
        state.askWhat = parsed.askWhat || 'random';
        state.count = parsed.count || 20;
        state.showTranslation = parsed.showTranslation !== false;
        state.queue = parsed.queue || [];
        state.pos = parsed.pos || 0;
        state.correct = parsed.correct || 0;
        state.wrong = parsed.wrong || 0;
        state.currentQuestion = parsed.currentQuestion || null;
        state.started = parsed.started || false;

        return state.started;
      } catch (e) {
        console.warn('Failed to load state:', e);
        localStorage.removeItem(STORAGE_KEY);
        return false;
      }
    }

    /**
     * Update mode button styles
     */
    function updateModeButtons() {
      const activeClass = 'border-primary bg-primary/10 text-primary';
      const inactiveClass = 'border-border bg-background text-muted-foreground';

      if (state.mode === 'typing') {
        modeTyping.className = `flex-1 px-3 py-2 text-sm font-medium rounded-lg border ${activeClass} transition hover:bg-primary/20`;
        modeChoice.className = `flex-1 px-3 py-2 text-sm font-medium rounded-lg border ${inactiveClass} transition hover:bg-muted`;
      } else {
        modeTyping.className = `flex-1 px-3 py-2 text-sm font-medium rounded-lg border ${inactiveClass} transition hover:bg-muted`;
        modeChoice.className = `flex-1 px-3 py-2 text-sm font-medium rounded-lg border ${activeClass} transition hover:bg-primary/20`;
      }
    }

    /**
     * Update UI from state (settings)
     */
    function updateSettingsUI() {
      updateModeButtons();
      askWhatSelect.value = state.askWhat;
      countInput.value = state.count;
      showTranslationCheckbox.checked = state.showTranslation;
    }

    /**
     * Update stats display
     */
    function updateStats() {
      const total = state.correct + state.wrong;
      const pct = total > 0 ? Math.round((state.correct / total) * 100) : 0;
      
      correctEl.textContent = state.correct;
      wrongEl.textContent = state.wrong;
      percentage.textContent = `${pct}%`;
      progressBar.style.width = `${pct}%`;
      
      // Update progress text
      progressText.textContent = `${state.pos} / ${state.count}`;
    }

    /**
     * Generate question queue
     */
    function generateQueue() {
      if (VERBS.length === 0) return;

      const indices = Array.from({ length: VERBS.length }, (_, i) => i);
      shuffleArray(indices);

      // If we need more questions than verbs, cycle through
      state.queue = [];
      while (state.queue.length < state.count) {
        const remaining = state.count - state.queue.length;
        const toAdd = indices.slice(0, Math.min(remaining, indices.length));
        state.queue.push(...toAdd);
        shuffleArray(indices);
      }
    }

    /**
     * Generate choices for choice mode
     */
    function generateChoices(correctAnswers, formKey) {
      const choices = new Set(correctAnswers);
      
      // Add random wrong answers
      const allAnswers = VERBS.map(v => {
        const val = v[formKey];
        return Array.isArray(val) ? val[0] : val;
      }).filter(a => a && !choices.has(a.toLowerCase()));
      
      shuffleArray(allAnswers);
      
      while (choices.size < 4 && allAnswers.length > 0) {
        choices.add(allAnswers.pop().toLowerCase());
      }

      return shuffleArray([...choices]);
    }

    /**
     * Show current question
     */
    function showQuestion() {
      if (state.pos >= state.count || state.pos >= state.queue.length) {
        showCompletion();
        return;
      }

      const verbIndex = state.queue[state.pos];
      const verb = VERBS[verbIndex];
      if (!verb) {
        showCompletion();
        return;
      }

      const askKey = getAskKey();
      const correctAnswers = getCorrectAnswers(verb, askKey);

      state.currentQuestion = {
        verbIndex,
        askKey,
        correctAnswers,
        displayAnswer: correctAnswers[0]
      };
      state.answered = false;

      // Update UI
      baseVerb.textContent = verb.base;
      ukVerb.textContent = state.showTranslation ? verb.translation : '';
      ukVerb.classList.toggle('hidden', !state.showTranslation);
      
      askLabel.textContent = `${i18n.give_answer || 'Give answer for'} ${formLabels[askKey]}`;
      
      // Hide hint initially
      hint.classList.add('hidden');
      hintText.textContent = getHintText(verb);

      // Reset feedback
      feedback.classList.add('hidden');

      // Show appropriate input mode
      if (state.mode === 'typing') {
        typingBox.classList.remove('hidden');
        choiceBox.classList.add('hidden');
        answerInput.value = '';
        answerInput.disabled = false;
        answerInput.focus();
      } else {
        typingBox.classList.add('hidden');
        choiceBox.classList.remove('hidden');
        renderChoices(correctAnswers, askKey);
      }

      // Reset buttons
      checkBtn.classList.remove('hidden');
      checkBtn.disabled = false;
      revealBtn.classList.remove('hidden');
      nextBtn.classList.add('hidden');

      updateStats();
      saveState();
    }

    /**
     * Render choice buttons
     */
    function renderChoices(correctAnswers, formKey) {
      const choices = generateChoices(correctAnswers, formKey);
      choiceBox.innerHTML = '';

      choices.forEach(choice => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'flex items-center justify-between gap-3 rounded-xl border border-border/80 bg-muted px-4 py-3 text-left text-foreground shadow-sm transition hover:-translate-y-0.5 hover:shadow focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary';
        btn.textContent = choice;
        btn.dataset.value = choice;
        btn.addEventListener('click', () => handleChoiceClick(btn, choice));
        choiceBox.appendChild(btn);
      });
    }

    /**
     * Handle choice button click
     */
    function handleChoiceClick(btn, choice) {
      if (state.answered) return;
      
      const isCorrect = state.currentQuestion.correctAnswers.includes(choice.toLowerCase().trim());
      handleAnswer(isCorrect, choice, btn);
    }

    /**
     * Check typed answer
     */
    function checkTypedAnswer() {
      if (state.answered) return;
      
      const answer = answerInput.value.trim().toLowerCase();
      if (!answer) return;

      const isCorrect = state.currentQuestion.correctAnswers.includes(answer);
      handleAnswer(isCorrect, answer, null);
    }

    /**
     * Handle answer (both modes)
     */
    function handleAnswer(isCorrect, userAnswer, clickedBtn) {
      state.answered = true;

      if (isCorrect) {
        state.correct++;
      } else {
        state.wrong++;
      }

      // Update UI
      if (state.mode === 'typing') {
        answerInput.disabled = true;
        answerInput.classList.remove('border-border/80');
        answerInput.classList.add(isCorrect ? 'border-success' : 'border-destructive');
      } else {
        // Highlight buttons
        choiceBox.querySelectorAll('button').forEach(btn => {
          btn.disabled = true;
          const val = btn.dataset.value.toLowerCase().trim();
          if (state.currentQuestion.correctAnswers.includes(val)) {
            btn.classList.add('choice-correct');
          } else if (btn === clickedBtn && !isCorrect) {
            btn.classList.add('choice-wrong', 'animate-shake');
          }
        });
      }

      // Show feedback
      showFeedback(isCorrect, userAnswer);

      // Show hint
      hint.classList.remove('hidden');

      // Update buttons
      checkBtn.classList.add('hidden');
      revealBtn.classList.add('hidden');
      nextBtn.classList.remove('hidden');

      updateStats();
      saveState();
    }

    /**
     * Show feedback panel
     */
    function showFeedback(isCorrect, userAnswer) {
      feedback.classList.remove('hidden');
      
      if (isCorrect) {
        feedbackChip.textContent = i18n.feedback_correct || 'Correct';
        feedbackChip.className = 'inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-semibold bg-success/10 text-success';
        feedbackTitle.textContent = `✓ ${userAnswer}`;
        feedbackBody.textContent = i18n.feedback_correct_msg || 'Great! Keep up the good work.';
      } else {
        feedbackChip.textContent = i18n.feedback_error || 'Error';
        feedbackChip.className = 'inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-semibold bg-destructive/10 text-destructive';
        const correctStr = state.currentQuestion.correctAnswers.join(' / ');
        feedbackTitle.textContent = `${i18n.correct_answer || 'Correct answer'}: ${correctStr}`;
        feedbackBody.textContent = i18n.feedback_error_msg || 'Pay attention to the correct answer.';
      }
    }

    /**
     * Reveal answer (skip question)
     */
    function revealAnswer() {
      if (state.answered) return;
      
      state.answered = true;
      state.wrong++;

      // Show hint
      hint.classList.remove('hidden');

      // Show feedback
      feedback.classList.remove('hidden');
      feedbackChip.textContent = i18n.revealed || 'Revealed';
      feedbackChip.className = 'inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-semibold bg-warning/10 text-warning';
      const correctStr = state.currentQuestion.correctAnswers.join(' / ');
      feedbackTitle.textContent = `${i18n.correct_answer || 'Correct answer'}: ${correctStr}`;
      feedbackBody.textContent = i18n.revealed_msg || 'The answer has been revealed.';

      // Disable input
      if (state.mode === 'typing') {
        answerInput.disabled = true;
      } else {
        choiceBox.querySelectorAll('button').forEach(btn => {
          btn.disabled = true;
          const val = btn.dataset.value.toLowerCase().trim();
          if (state.currentQuestion.correctAnswers.includes(val)) {
            btn.classList.add('choice-correct');
          }
        });
      }

      // Update buttons
      checkBtn.classList.add('hidden');
      revealBtn.classList.add('hidden');
      nextBtn.classList.remove('hidden');

      updateStats();
      saveState();
    }

    /**
     * Go to next question
     */
    function nextQuestion() {
      state.pos++;
      showQuestion();
    }

    /**
     * Show completion screen
     */
    function showCompletion() {
      state.started = false;
      questionArea.classList.add('hidden');
      doneBox.classList.remove('hidden');
      restartBtn.classList.remove('hidden');

      const total = state.correct + state.wrong;
      const pct = total > 0 ? Math.round((state.correct / total) * 100) : 0;
      doneText.textContent = (i18n.result || 'Result: :correct of :total (:pct%)')
        .replace(':correct', state.correct)
        .replace(':total', total)
        .replace(':pct', pct);

      // Clear saved state
      localStorage.removeItem(STORAGE_KEY);
    }

    /**
     * Start test
     */
    function startTest() {
      if (VERBS.length === 0) {
        alert(i18n.no_verbs || 'No verbs available. Please add irregular verbs to the database.');
        return;
      }

      // Read settings - use actual state tracking
      state.askWhat = askWhatSelect.value;
      state.count = Math.min(Math.max(parseInt(countInput.value) || 20, 5), 100);
      state.showTranslation = showTranslationCheckbox.checked;
      state.pos = 0;
      state.correct = 0;
      state.wrong = 0;
      state.started = true;
      state.currentQuestion = null;

      generateQueue();

      // Update UI
      settingsPanel.classList.add('hidden');
      questionArea.classList.remove('hidden');
      doneBox.classList.add('hidden');
      feedback.classList.add('hidden');
      restartBtn.classList.remove('hidden');

      showQuestion();
    }

    /**
     * Restart test
     */
    function restartTest() {
      state.started = false;
      localStorage.removeItem(STORAGE_KEY);

      // Reset UI
      settingsPanel.classList.remove('hidden');
      questionArea.classList.add('hidden');
      doneBox.classList.add('hidden');
      feedback.classList.add('hidden');
      restartBtn.classList.add('hidden');

      // Reset stats display
      state.correct = 0;
      state.wrong = 0;
      state.pos = 0;
      updateStats();
    }

    /**
     * Resume test from saved state
     */
    function resumeTest() {
      settingsPanel.classList.add('hidden');
      questionArea.classList.remove('hidden');
      doneBox.classList.add('hidden');
      restartBtn.classList.remove('hidden');

      showQuestion();
    }

    // Event listeners
    startBtn.addEventListener('click', startTest);
    restartBtn.addEventListener('click', restartTest);
    checkBtn.addEventListener('click', checkTypedAnswer);
    revealBtn.addEventListener('click', revealAnswer);
    nextBtn.addEventListener('click', nextQuestion);

    modeTyping.addEventListener('click', () => {
      state.mode = 'typing';
      updateModeButtons();
    });

    modeChoice.addEventListener('click', () => {
      state.mode = 'choice';
      updateModeButtons();
    });

    // Enter key to check answer in typing mode
    answerInput.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        if (!state.answered) {
          checkTypedAnswer();
        } else {
          nextQuestion();
        }
      }
    });

    // Translation toggle
    showTranslationCheckbox.addEventListener('change', () => {
      state.showTranslation = showTranslationCheckbox.checked;
      if (state.started && state.currentQuestion) {
        const verb = VERBS[state.currentQuestion.verbIndex];
        ukVerb.textContent = state.showTranslation ? verb.translation : '';
        ukVerb.classList.toggle('hidden', !state.showTranslation);
      }
      saveState();
    });

    // Initialize
    updateSettingsUI();
    updateStats();

    // Try to restore state
    if (loadState() && state.started) {
      updateSettingsUI();
      resumeTest();
    }
  });
</script>
@endsection
