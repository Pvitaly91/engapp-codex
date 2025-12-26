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

    // Read settings
    state.mode = modeTyping.classList.contains('bg-primary/10') ? 'typing' : 'choice';
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

    if (state.currentQuestion) {
      showQuestion();
    } else {
      showQuestion();
    }
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
