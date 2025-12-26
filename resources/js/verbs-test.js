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
    doneText: document.getElementById('doneText')
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
    showElement(els.doneBox);
    
    const total = state.queue.length;
    els.doneText.textContent = `Тест завершено! Правильних відповідей: ${state.correct} з ${total}`;
    
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
    showElement(els.startBtn);
    hideElement(els.restartBtn);
    
    updateProgress();
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
