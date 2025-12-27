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

    .animate-soft { animation: fade-in-soft 280ms ease; }
    .animate-pop { animation: pop-in 220ms ease; }

    .choice-correct {
      border-color: rgba(34, 197, 94, 0.5);
      background: linear-gradient(135deg, rgba(34, 197, 94, 0.15), rgba(34, 197, 94, 0.05));
      color: rgb(21, 128, 61);
    }

    .choice-wrong {
      border-color: rgba(239, 68, 68, 0.5);
      background: linear-gradient(135deg, rgba(239, 68, 68, 0.15), rgba(239, 68, 68, 0.05));
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
    <!-- Restart button in header (visible during test) -->
    <div id="headerRestartBtn" class="hidden">
      <button type="button" id="headerRestart" class="inline-flex items-center gap-2 rounded-xl border border-border bg-background px-4 py-2 text-sm font-semibold text-foreground shadow-sm transition hover:-translate-y-0.5 hover:shadow hover:border-primary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        {{ __('verbs_test.restart') }}
      </button>
    </div>
  </div>

  @if(empty($verbs))
  <!-- No verbs state -->
  <div class="rounded-2xl bg-card p-8 shadow-soft border border-border/70 text-center">
    <h2 class="text-2xl font-semibold text-foreground mb-2">{{ __('verbs_test.no_verbs') }}</h2>
    <p class="text-muted-foreground">{{ __('verbs_test.no_verbs_message') }}</p>
  </div>
  @else
  
  <div class="grid gap-6 lg:grid-cols-[2fr_1fr]">
    <!-- Settings Card -->
    <div class="rounded-2xl bg-card p-6 shadow-soft border border-border/70">
      <h2 class="text-xl font-semibold text-foreground mb-4">{{ __('verbs_test.settings') }}</h2>
      
      <div class="space-y-4" id="settings-panel">
        <!-- Mode -->
        <div class="space-y-2">
          <label class="text-sm font-semibold text-muted-foreground">{{ __('verbs_test.mode') }}</label>
          <div class="flex gap-2">
            <button type="button" class="mode-btn flex-1 px-4 py-2 rounded-lg border border-border bg-background text-sm font-medium transition hover:border-primary" data-mode="typing">
              {{ __('verbs_test.mode_typing') }}
            </button>
            <button type="button" class="mode-btn flex-1 px-4 py-2 rounded-lg border border-border bg-background text-sm font-medium transition hover:border-primary" data-mode="choice">
              {{ __('verbs_test.mode_choice') }}
            </button>
          </div>
        </div>

        <!-- Ask what -->
        <div class="space-y-2">
          <label class="text-sm font-semibold text-muted-foreground">{{ __('verbs_test.ask_what') }}</label>
          <select id="askWhat" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm font-medium text-foreground shadow-sm transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
            <option value="random">{{ __('verbs_test.ask_random') }}</option>
            <option value="f1">{{ __('verbs_test.ask_f1') }}</option>
            <option value="f2">{{ __('verbs_test.ask_f2') }}</option>
            <option value="f3">{{ __('verbs_test.ask_f3') }}</option>
            <option value="f4">{{ __('verbs_test.ask_f4') }}</option>
          </select>
        </div>

        <!-- Count -->
        <div class="space-y-2">
          <label for="count" class="text-sm font-semibold text-muted-foreground">{{ __('verbs_test.question_count') }}</label>
          <input type="number" id="count" value="10" min="1" max="100" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm font-medium text-foreground shadow-sm transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
        </div>

        <!-- Show translation -->
        <div class="flex items-center gap-2">
          <input type="checkbox" id="showUk" class="h-4 w-4 rounded border-border text-primary focus:ring-2 focus:ring-primary/20">
          <label for="showUk" class="text-sm font-medium text-foreground">{{ __('verbs_test.show_translation') }}</label>
        </div>

        <!-- Start button -->
        <button type="button" id="startBtn" class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-6 py-3 text-sm font-semibold text-primary-foreground shadow-sm transition hover:-translate-y-0.5 hover:shadow">
          {{ __('verbs_test.start') }}
        </button>

        <!-- Restart button (hidden initially) -->
        <button type="button" id="restartBtn" class="hidden w-full inline-flex items-center justify-center gap-2 rounded-xl border border-border bg-background px-6 py-3 text-sm font-semibold text-foreground shadow-sm transition hover:-translate-y-0.5 hover:shadow">
          {{ __('verbs_test.restart') }}
        </button>
      </div>
    </div>

    <!-- Stats Card -->
    <div class="rounded-2xl bg-card p-6 shadow-soft border border-border/70">
      <h2 class="text-xl font-semibold text-foreground mb-4">{{ __('verbs_test.progress') }}</h2>
      
      <div class="space-y-4">
        <!-- Progress bar -->
        <div class="space-y-2">
          <div class="flex justify-between text-sm text-muted-foreground">
            <span id="progressText">0 / 0</span>
          </div>
          <div class="h-2 rounded-full bg-muted overflow-hidden">
            <div id="progressBar" class="h-full bg-primary transition-all duration-300" style="width: 0%"></div>
          </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 gap-4">
          <div class="rounded-lg bg-success/10 border border-success/30 p-3 text-center">
            <div class="text-2xl font-bold text-success" id="correct">0</div>
            <div class="text-xs text-muted-foreground">{{ __('verbs_test.correct') }}</div>
          </div>
          <div class="rounded-lg bg-destructive/10 border border-destructive/30 p-3 text-center">
            <div class="text-2xl font-bold text-destructive" id="wrong">0</div>
            <div class="text-xs text-muted-foreground">{{ __('verbs_test.wrong') }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Question Card -->
  <div id="questionBox" class="hidden rounded-2xl bg-card p-6 shadow-soft border border-border/70">
    <div class="space-y-6">
      <!-- Question header -->
      <div class="space-y-2">
        <div class="inline-flex items-center gap-2 rounded-full bg-primary/10 px-3 py-1 text-sm font-medium text-primary">
          <span class="h-2 w-2 rounded-full bg-primary"></span>
          <span>{{ __('verbs_test.question') }}</span>
        </div>
        
        <div class="space-y-1">
          <p id="askLabel" class="text-sm text-muted-foreground">{{ __('verbs_test.give_answer_for') }}</p>
          <div class="flex items-baseline gap-4">
            <div id="baseVerb" class="text-3xl font-semibold text-foreground"></div>
            <div id="ukVerb" class="text-lg text-muted-foreground hidden"></div>
          </div>
        </div>
      </div>

      <!-- Hint -->
      <div class="rounded-lg bg-muted/50 border border-border/50 p-4">
        <p class="text-xs uppercase tracking-wider text-muted-foreground mb-2">{{ __('verbs_test.hint') }}</p>
        <p id="hint" class="text-sm font-mono text-foreground"></p>
      </div>

      <!-- Typing mode -->
      <div id="typingBox" class="hidden space-y-3">
        <input type="text" id="answerInput" class="w-full rounded-lg border border-border bg-background px-4 py-3 text-lg font-medium text-foreground shadow-sm transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20" placeholder="Type your answer...">
        
        <div class="flex gap-2">
          <button type="button" id="checkBtn" class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground shadow-sm transition hover:shadow">
            {{ __('verbs_test.check') }}
          </button>
          <button type="button" id="revealBtn" class="px-4 py-2 rounded-lg border border-border bg-background text-sm font-semibold text-foreground shadow-sm transition hover:shadow">
            {{ __('verbs_test.reveal') }}
          </button>
        </div>
      </div>

      <!-- Choice mode -->
      <div id="choiceBox" class="hidden">
        <div id="choices" class="grid grid-cols-2 gap-3"></div>
      </div>

      <!-- Feedback -->
      <div id="feedback" class="hidden rounded-lg p-4 text-sm font-medium"></div>

      <!-- Next button -->
      <button type="button" id="nextBtn" class="hidden w-full inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground shadow-sm transition hover:shadow">
        {{ __('verbs_test.next') }}
      </button>
    </div>
  </div>

  <!-- Done Card -->
  <div id="doneBox" class="hidden rounded-2xl bg-card p-8 shadow-soft border border-border/70 text-center">
    <div class="mb-6">
      <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-success/10 mb-4">
        <svg class="w-8 h-8 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      </div>
      <h2 class="text-2xl font-semibold text-foreground mb-2">{{ __('verbs_test.done') }}</h2>
      <p id="doneText" class="text-lg text-muted-foreground mb-6"></p>
    </div>
    
    <!-- Summary stats -->
    <div class="grid grid-cols-2 gap-4 mb-6 max-w-md mx-auto">
      <div class="rounded-lg bg-success/10 border border-success/30 p-4">
        <div class="text-3xl font-bold text-success" id="finalCorrect">0</div>
        <div class="text-sm text-muted-foreground">{{ __('verbs_test.correct') }}</div>
      </div>
      <div class="rounded-lg bg-destructive/10 border border-destructive/30 p-4">
        <div class="text-3xl font-bold text-destructive" id="finalWrong">0</div>
        <div class="text-sm text-muted-foreground">{{ __('verbs_test.wrong') }}</div>
      </div>
    </div>
    
    <!-- Action buttons -->
    <div class="flex flex-col sm:flex-row gap-3 justify-center">
      <button type="button" id="doneRestartBtn" class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-6 py-3 text-sm font-semibold text-primary-foreground shadow-sm transition hover:-translate-y-0.5 hover:shadow">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        {{ __('verbs_test.start_again') }}
      </button>
      <button type="button" id="doneSettingsBtn" class="inline-flex items-center justify-center gap-2 rounded-xl border border-border bg-background px-6 py-3 text-sm font-semibold text-foreground shadow-sm transition hover:-translate-y-0.5 hover:shadow">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        {{ __('verbs_test.change_settings') }}
      </button>
    </div>
  </div>

  @endif
</div>

<script>
  // Pass verbs data to JavaScript
  window.__VERBS__ = @json($verbs);

  // Irregular Verbs Test JS Logic
  (function() {
    'use strict';

    const STORAGE_KEY = 'verbs_test_state_v1';
    const VERBS = window.__VERBS__ || [];

    if (VERBS.length === 0) {
      console.warn('No verbs data available');
      return;
    }

    // State
    let state = {
      settings: {
        mode: 'typing',
        askWhat: 'random',
        count: 10,
        showTranslation: false
      },
      queue: [],
      pos: 0,
      correct: 0,
      wrong: 0,
      currentQuestion: null,
      isActive: false
    };

    // DOM elements
    const els = {
      // Settings
      modeBtns: document.querySelectorAll('.mode-btn'),
      askWhat: document.getElementById('askWhat'),
      count: document.getElementById('count'),
      showUk: document.getElementById('showUk'),
      startBtn: document.getElementById('startBtn'),
      restartBtn: document.getElementById('restartBtn'),
      settingsPanel: document.getElementById('settings-panel'),
      
      // Header buttons
      headerRestartBtn: document.getElementById('headerRestartBtn'),
      headerRestart: document.getElementById('headerRestart'),
      
      // Progress
      progressText: document.getElementById('progressText'),
      progressBar: document.getElementById('progressBar'),
      correctEl: document.getElementById('correct'),
      wrongEl: document.getElementById('wrong'),
      
      // Question
      questionBox: document.getElementById('questionBox'),
      askLabel: document.getElementById('askLabel'),
      baseVerb: document.getElementById('baseVerb'),
      ukVerb: document.getElementById('ukVerb'),
      hint: document.getElementById('hint'),
      
      // Typing mode
      typingBox: document.getElementById('typingBox'),
      answerInput: document.getElementById('answerInput'),
      checkBtn: document.getElementById('checkBtn'),
      revealBtn: document.getElementById('revealBtn'),
      
      // Choice mode
      choiceBox: document.getElementById('choiceBox'),
      choices: document.getElementById('choices'),
      
      // Feedback
      feedback: document.getElementById('feedback'),
      nextBtn: document.getElementById('nextBtn'),
      
      // Done
      doneBox: document.getElementById('doneBox'),
      doneText: document.getElementById('doneText'),
      finalCorrect: document.getElementById('finalCorrect'),
      finalWrong: document.getElementById('finalWrong'),
      doneRestartBtn: document.getElementById('doneRestartBtn'),
      doneSettingsBtn: document.getElementById('doneSettingsBtn')
    };

    // Utility functions
    function shuffle(array) {
      const arr = [...array];
      for (let i = arr.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [arr[i], arr[j]] = [arr[j], arr[i]];
      }
      return arr;
    }

    function normalize(str) {
      return str.trim().toLowerCase();
    }

    function saveState() {
      try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
      } catch (e) {
        console.error('Failed to save state:', e);
      }
    }

    function loadState() {
      try {
        const saved = localStorage.getItem(STORAGE_KEY);
        if (saved) {
          const parsed = JSON.parse(saved);
          // Validate that verbs list hasn't changed
          if (parsed.queue && parsed.queue.length > 0) {
            state = { ...state, ...parsed };
            return true;
          }
        }
      } catch (e) {
        console.error('Failed to load state:', e);
      }
      return false;
    }

    function clearState() {
      try {
        localStorage.removeItem(STORAGE_KEY);
      } catch (e) {
        console.error('Failed to clear state:', e);
      }
    }

    // UI updates
    function updateProgress() {
      const total = state.queue.length;
      const current = state.pos + 1;
      
      els.progressText.textContent = `${current} / ${total}`;
      els.progressBar.style.width = `${(current / total) * 100}%`;
      els.correctEl.textContent = state.correct;
      els.wrongEl.textContent = state.wrong;
    }

    function updateModeButtons() {
      els.modeBtns.forEach(btn => {
        const mode = btn.dataset.mode;
        if (mode === state.settings.mode) {
          btn.classList.add('border-primary', 'bg-primary/10', 'text-primary');
        } else {
          btn.classList.remove('border-primary', 'bg-primary/10', 'text-primary');
        }
      });
    }

    function showElement(el) {
      if (el) el.classList.remove('hidden');
    }

    function hideElement(el) {
      if (el) el.classList.add('hidden');
    }

    // Test logic
    function generateQueue() {
      const count = parseInt(state.settings.count) || 10;
      let queue = [];
      
      // Create queue (repeat verbs if count > verbs.length)
      while (queue.length < count) {
        const remaining = count - queue.length;
        const chunk = shuffle(VERBS).slice(0, Math.min(remaining, VERBS.length));
        queue = queue.concat(chunk.map((v, i) => VERBS.indexOf(v)));
      }
      
      return queue;
    }

    function getFormKey() {
      const askWhat = state.settings.askWhat;
      if (askWhat === 'random') {
        const forms = ['f1', 'f2', 'f3', 'f4'];
        return forms[Math.floor(Math.random() * forms.length)];
      }
      return askWhat;
    }

    function getCorrectAnswer(verb, formKey) {
      const value = verb[formKey];
      return Array.isArray(value) ? value : [value];
    }

    function formatHint(verb) {
      const f2Str = Array.isArray(verb.f2) ? verb.f2.join(' / ') : verb.f2;
      const f3Str = Array.isArray(verb.f3) ? verb.f3.join(' / ') : verb.f3;
      return `Base: ${verb.base} • Form 1: ${verb.f1} • Form 2: ${f2Str} • Form 3: ${f3Str} • Form 4: ${verb.f4}`;
    }

    function generateChoices(correctAnswers, allVerbs) {
      const choices = new Set();
      
      // Add one correct answer
      const correctAnswer = correctAnswers[Math.floor(Math.random() * correctAnswers.length)];
      choices.add(correctAnswer);
      
      // Add 3 wrong answers
      const wrongPool = [];
      allVerbs.forEach(v => {
        ['f1', 'f2', 'f3', 'f4'].forEach(key => {
          const value = v[key];
          const forms = Array.isArray(value) ? value : [value];
          forms.forEach(form => {
            if (!correctAnswers.includes(form)) {
              wrongPool.push(form);
            }
          });
        });
      });
      
      const shuffledWrong = shuffle(wrongPool);
      let i = 0;
      while (choices.size < 4 && i < shuffledWrong.length) {
        choices.add(shuffledWrong[i]);
        i++;
      }
      
      return shuffle([...choices]);
    }

    function startTest() {
      state.queue = generateQueue();
      state.pos = 0;
      state.correct = 0;
      state.wrong = 0;
      state.isActive = true;
      
      // Hide settings, show question box
      hideElement(els.startBtn);
      showElement(els.restartBtn);
      showElement(els.headerRestartBtn);
      showElement(els.questionBox);
      hideElement(els.doneBox);
      
      updateProgress();
      showQuestion();
      saveState();
    }

    function showQuestion() {
      if (state.pos >= state.queue.length) {
        endTest();
        return;
      }
      
      const verbIndex = state.queue[state.pos];
      const verb = VERBS[verbIndex];
      const formKey = getFormKey();
      const correctAnswers = getCorrectAnswer(verb, formKey);
      
      state.currentQuestion = {
        verb,
        formKey,
        correctAnswers
      };
      
      // Update UI
      els.baseVerb.textContent = verb.base;
      
      if (state.settings.showTranslation) {
        els.ukVerb.textContent = `(${verb.translation})`;
        showElement(els.ukVerb);
      } else {
        hideElement(els.ukVerb);
      }
      
      const formLabels = {
        f1: 'Form 1 (3rd person singular)',
        f2: 'Form 2 (Past)',
        f3: 'Form 3 (Past Participle)',
        f4: 'Form 4 (-ing form)'
      };
      els.askLabel.textContent = formLabels[formKey] || formKey;
      
      els.hint.textContent = formatHint(verb);
      
      // Reset feedback
      hideElement(els.feedback);
      hideElement(els.nextBtn);
      
      // Show appropriate mode
      if (state.settings.mode === 'typing') {
        showElement(els.typingBox);
        hideElement(els.choiceBox);
        els.answerInput.value = '';
        els.answerInput.focus();
        showElement(els.checkBtn);
        showElement(els.revealBtn);
      } else {
        hideElement(els.typingBox);
        showElement(els.choiceBox);
        
        // Generate choices
        const choices = generateChoices(correctAnswers, VERBS);
        els.choices.innerHTML = choices.map(choice => 
          `<button type="button" class="choice-btn px-4 py-3 rounded-lg border border-border bg-background text-foreground font-medium transition hover:border-primary hover:shadow" data-answer="${choice}">${choice}</button>`
        ).join('');
        
        // Add click handlers
        els.choices.querySelectorAll('.choice-btn').forEach(btn => {
          btn.addEventListener('click', () => checkAnswer(btn.dataset.answer, btn));
        });
      }
      
      updateProgress();
      saveState();
    }

    function checkAnswer(userAnswer, choiceBtn = null) {
      const { correctAnswers } = state.currentQuestion;
      const normalized = normalize(userAnswer);
      const isCorrect = correctAnswers.some(ans => normalize(ans) === normalized);
      
      // Show feedback
      if (isCorrect) {
        state.correct++;
        els.feedback.textContent = '✓ Правильно!';
        els.feedback.className = 'rounded-lg p-4 text-sm font-medium bg-success/10 border border-success/30 text-success';
        if (choiceBtn) {
          choiceBtn.classList.add('choice-correct');
        }
      } else {
        state.wrong++;
        const correctStr = correctAnswers.join(' / ');
        els.feedback.textContent = `✗ Помилка. Правильна відповідь: ${correctStr}`;
        els.feedback.className = 'rounded-lg p-4 text-sm font-medium bg-destructive/10 border border-destructive/30 text-destructive';
        if (choiceBtn) {
          choiceBtn.classList.add('choice-wrong');
        }
      }
      
      showElement(els.feedback);
      showElement(els.nextBtn);
      
      // Disable input
      if (state.settings.mode === 'typing') {
        els.answerInput.disabled = true;
        hideElement(els.checkBtn);
        hideElement(els.revealBtn);
      } else {
        els.choices.querySelectorAll('.choice-btn').forEach(btn => {
          btn.disabled = true;
        });
      }
      
      updateProgress();
      saveState();
    }

    function revealAnswer() {
      const { correctAnswers } = state.currentQuestion;
      state.wrong++;
      
      const correctStr = correctAnswers.join(' / ');
      els.feedback.textContent = `Правильна відповідь: ${correctStr}`;
      els.feedback.className = 'rounded-lg p-4 text-sm font-medium bg-muted border border-border text-foreground';
      
      showElement(els.feedback);
      showElement(els.nextBtn);
      
      els.answerInput.disabled = true;
      hideElement(els.checkBtn);
      hideElement(els.revealBtn);
      
      updateProgress();
      saveState();
    }

    function nextQuestion() {
      state.pos++;
      
      if (state.settings.mode === 'typing') {
        els.answerInput.disabled = false;
      }
      
      showQuestion();
    }

    function endTest() {
      state.isActive = false;
      hideElement(els.questionBox);
      hideElement(els.headerRestartBtn);
      showElement(els.doneBox);
      
      const total = state.queue.length;
      els.doneText.textContent = `Тест завершено! Правильних відповідей: ${state.correct} з ${total}`;
      els.finalCorrect.textContent = state.correct;
      els.finalWrong.textContent = state.wrong;
      
      // Scroll to completion screen
      els.doneBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
      
      clearState();
    }

    function restartTest() {
      clearState();
      state.pos = 0;
      state.correct = 0;
      state.wrong = 0;
      state.isActive = false;
      
      hideElement(els.questionBox);
      hideElement(els.doneBox);
      hideElement(els.headerRestartBtn);
      showElement(els.startBtn);
      hideElement(els.restartBtn);
      
      updateProgress();
      
      // Scroll to top
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    
    function backToSettings() {
      clearState();
      state.pos = 0;
      state.correct = 0;
      state.wrong = 0;
      state.isActive = false;
      
      hideElement(els.questionBox);
      hideElement(els.doneBox);
      hideElement(els.headerRestartBtn);
      showElement(els.startBtn);
      hideElement(els.restartBtn);
      
      updateProgress();
      
      // Scroll to settings
      els.settingsPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    // Event listeners
    els.modeBtns.forEach(btn => {
      btn.addEventListener('click', () => {
        state.settings.mode = btn.dataset.mode;
        updateModeButtons();
        if (!state.isActive) saveState();
      });
    });

    els.askWhat.addEventListener('change', () => {
      state.settings.askWhat = els.askWhat.value;
      if (!state.isActive) saveState();
    });

    els.count.addEventListener('change', () => {
      state.settings.count = parseInt(els.count.value) || 10;
      if (!state.isActive) saveState();
    });

    els.showUk.addEventListener('change', () => {
      state.settings.showTranslation = els.showUk.checked;
      if (state.isActive && state.currentQuestion) {
        // Update translation visibility immediately
        if (state.settings.showTranslation) {
          els.ukVerb.textContent = `(${state.currentQuestion.verb.translation})`;
          showElement(els.ukVerb);
        } else {
          hideElement(els.ukVerb);
        }
      }
      saveState();
    });

    els.startBtn.addEventListener('click', startTest);
    els.restartBtn.addEventListener('click', restartTest);
    els.headerRestart.addEventListener('click', restartTest);
    els.doneRestartBtn.addEventListener('click', startTest);
    els.doneSettingsBtn.addEventListener('click', backToSettings);

    els.checkBtn.addEventListener('click', () => {
      const answer = els.answerInput.value.trim();
      if (answer) {
        checkAnswer(answer);
      }
    });

    els.answerInput.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        const answer = els.answerInput.value.trim();
        if (answer) {
          checkAnswer(answer);
        }
      }
    });

    els.revealBtn.addEventListener('click', revealAnswer);
    els.nextBtn.addEventListener('click', nextQuestion);

    // Initialize
    function init() {
      // Try to load saved state
      if (loadState() && state.isActive) {
        // Restore UI
        hideElement(els.startBtn);
        showElement(els.restartBtn);
        showElement(els.headerRestartBtn);
        showElement(els.questionBox);
        updateProgress();
        showQuestion();
      }
      
      // Update UI from settings
      els.askWhat.value = state.settings.askWhat;
      els.count.value = state.settings.count;
      els.showUk.checked = state.settings.showTranslation;
      updateModeButtons();
      updateProgress();
    }

    init();
  })();
</script>
@endsection
