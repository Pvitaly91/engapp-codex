@extends('layouts.engram')

@section('title', __('verbs.title'))

@section('content')
  <div class="space-y-8">
    {{-- Hero Section --}}
    <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-600 via-brand-500 to-brand-400 p-8 text-white shadow-card">
      {{-- Decorative background elements --}}
      <div class="absolute inset-0 opacity-20">
        <div class="absolute -top-20 -right-20 w-64 h-64 bg-white/20 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-20 -left-20 w-48 h-48 bg-white/15 rounded-full blur-3xl"></div>
      </div>
      
      <div class="relative">
        <span class="inline-flex items-center gap-2 rounded-full bg-white/20 backdrop-blur-sm px-4 py-2 text-sm font-semibold mb-4">
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
          {{ __('verbs.subtitle') }}
        </span>
        <h1 class="text-3xl md:text-4xl font-bold mb-3">{{ __('verbs.title') }}</h1>
        <p class="text-white/80 max-w-3xl text-lg">{{ __('verbs.description') }}</p>
      </div>
    </section>

    <div class="flex flex-col gap-4 lg:grid lg:grid-cols-[1.5fr_0.9fr]">
      <div class="space-y-4 order-2 lg:order-1">
        <div class="rounded-2xl border border-[var(--border)] bg-[var(--card)] p-5 shadow-sm">
          <div class="flex items-center justify-between gap-3">
            <div>
              <h2 class="text-lg font-bold text-[var(--fg)]">{{ __('verbs.settings') }}</h2>
              <span class="rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold text-brand-700" id="verbs-count-badge">{{ count($verbs) }} {{ __('verbs.verbs_total') }}</span>
            </div>
            <button id="settingsToggle" class="inline-flex items-center gap-2 rounded-full border border-brand-200 bg-brand-50 px-3 py-2 text-xs font-semibold text-brand-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow">
              <span class="h-2 w-2 rounded-full bg-brand-600 shadow-inner"></span>
              <span id="settingsToggleText" class="uppercase tracking-[0.08em]">{{ __('verbs.settings_hide') }}</span>
            </button>
          </div>
          <div id="settingsBody" class="mt-4 grid gap-4 md:grid-cols-2">
            <div class="space-y-2">
              <p class="text-sm font-semibold text-[var(--muted)]">{{ __('verbs.mode') }}</p>
              <div id="modeButtons" class="grid grid-cols-2 gap-2">
                <button type="button" data-mode-button value="hard" class="w-full rounded-full border border-[var(--border)] bg-[var(--card)] px-3 py-2 text-sm font-semibold text-[var(--fg)] shadow-sm transition hover:-translate-y-0.5 hover:shadow hover:border-brand-400">{{ __('verbs.mode_typing') }}</button>
                <button type="button" data-mode-button value="medium" class="w-full rounded-full border border-[var(--border)] bg-[var(--card)] px-3 py-2 text-sm font-semibold text-[var(--fg)] shadow-sm transition hover:-translate-y-0.5 hover:shadow hover:border-brand-400">{{ __('verbs.mode_medium') }}</button>
                <button type="button" data-mode-button value="easy" class="w-full rounded-full border border-[var(--border)] bg-[var(--card)] px-3 py-2 text-sm font-semibold text-[var(--fg)] shadow-sm transition hover:-translate-y-0.5 hover:shadow hover:border-brand-400">{{ __('verbs.mode_choice') }}</button>
              </div>
            </div>
            <div class="space-y-2">
              <p class="text-sm font-semibold text-[var(--muted)]">{{ __('verbs.ask_what') }}</p>
              <div id="askButtons" class="grid grid-cols-2 gap-2 md:grid-cols-3">
                <button type="button" data-ask-button value="random" class="w-full rounded-full border border-[var(--border)] bg-[var(--card)] px-3 py-2 text-sm font-semibold text-[var(--fg)] shadow-sm transition hover:-translate-y-0.5 hover:shadow hover:border-brand-400">{{ __('verbs.ask_random') }}</button>
                <button type="button" data-ask-button value="f1" class="w-full rounded-full border border-[var(--border)] bg-[var(--card)] px-3 py-2 text-sm font-semibold text-[var(--fg)] shadow-sm transition hover:-translate-y-0.5 hover:shadow hover:border-brand-400">{{ __('verbs.ask_f1') }}</button>
                <button type="button" data-ask-button value="f2" class="w-full rounded-full border border-[var(--border)] bg-[var(--card)] px-3 py-2 text-sm font-semibold text-[var(--fg)] shadow-sm transition hover:-translate-y-0.5 hover:shadow hover:border-brand-400">{{ __('verbs.ask_f2') }}</button>
                <button type="button" data-ask-button value="f3" class="w-full rounded-full border border-[var(--border)] bg-[var(--card)] px-3 py-2 text-sm font-semibold text-[var(--fg)] shadow-sm transition hover:-translate-y-0.5 hover:shadow hover:border-brand-400">{{ __('verbs.ask_f3') }}</button>
                <button type="button" data-ask-button value="f4" class="w-full rounded-full border border-[var(--border)] bg-[var(--card)] px-3 py-2 text-sm font-semibold text-[var(--fg)] shadow-sm transition hover:-translate-y-0.5 hover:shadow hover:border-brand-400">{{ __('verbs.ask_f4') }}</button>
              </div>
            </div>
            <div class="flex items-center gap-3 rounded-2xl border border-brand-100 bg-brand-50/50 px-4 py-3">
              <input id="showUk" type="checkbox" class="h-4 w-4 rounded border-brand-300 text-brand-600 focus:ring-brand-500" />
              <label for="showUk" class="text-sm font-semibold text-brand-900">{{ __('verbs.show_translation') }}</label>
            </div>
          </div>
          <div class="mt-4 flex flex-wrap gap-3">
            <button id="startBtn" class="inline-flex items-center gap-2 rounded-full bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md hover:bg-brand-700">
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/></svg>
              {{ __('verbs.start') }}
            </button>
            <button id="restartBtn" class="inline-flex items-center gap-2 rounded-full border border-[var(--border)] bg-[var(--card)] px-5 py-2.5 text-sm font-semibold text-[var(--fg)] shadow-sm transition hover:-translate-y-0.5 hover:shadow">
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
              {{ __('verbs.restart') }}
            </button>
          </div>
        </div>

        <div id="questionCard" class="rounded-2xl border border-[var(--border)] bg-[var(--card)] p-6 shadow-sm space-y-4 hidden">
          <div class="flex items-center justify-between gap-3">
            <div class="space-y-2">
              <p class="text-xs uppercase tracking-[0.16em] text-[var(--muted)]">{{ __('verbs.question') }}</p>
              <span id="askLabel" class="inline-flex items-center gap-2 rounded-full bg-brand-50 px-3 py-1.5 text-sm font-semibold text-brand-700 border border-brand-200"></span>
            </div>
            <div class="text-sm font-medium text-[var(--muted)]">
              <span id="progressText">0 / 0</span>
            </div>
          </div>

          <div class="space-y-3 rounded-2xl border border-brand-100 bg-brand-50/50 px-5 py-4">
            <p class="text-sm font-medium text-brand-700">{{ __('verbs.answer_for') }}</p>
            <div class="flex flex-col gap-1">
              <div id="baseVerb" class="text-3xl font-bold text-brand-900">—</div>
              <div id="ukVerb" class="text-sm text-brand-600"></div>
            </div>
          </div>

          <div class="rounded-2xl border border-[var(--border)] bg-[var(--card)] px-5 py-4">
            <p class="text-xs uppercase tracking-[0.12em] text-[var(--muted)]">{{ __('verbs.hint') }}</p>
            <p id="hint" class="mt-1 text-sm text-[var(--fg)]"></p>
          </div>

          <div id="typingBox" class="space-y-3">
            <label for="answerInput" class="text-sm font-semibold text-[var(--muted)]">{{ __('verbs.type_answer') }}</label>
            <input id="answerInput" type="text" autocomplete="off" class="w-full rounded-2xl border border-[var(--border)] bg-[var(--card)] px-4 py-3 text-lg font-semibold text-[var(--fg)] shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-200" />
            <div id="suggestionsBox" class="hidden rounded-2xl border border-[var(--border)] bg-[var(--card)] p-2 text-sm text-[var(--fg)] space-y-1"></div>
          </div>

          <div id="choiceBox" class="grid gap-3 md:grid-cols-2"></div>

          <div id="controlButtons" class="flex flex-wrap items-center gap-3">
            <button id="checkBtn" class="inline-flex items-center gap-2 rounded-full bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md hover:bg-brand-700">
              {{ __('verbs.check') }}
            </button>
            <button id="revealBtn" class="inline-flex items-center gap-2 rounded-full border border-[var(--border)] bg-[var(--card)] px-5 py-2.5 text-sm font-semibold text-[var(--fg)] shadow-sm transition hover:-translate-y-0.5 hover:shadow">
              {{ __('verbs.reveal') }}
            </button>
            <button id="nextBtn" class="inline-flex items-center gap-2 rounded-full border border-brand-200 bg-brand-50 px-5 py-2.5 text-sm font-semibold text-brand-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow">
              {{ __('verbs.next') }}
            </button>
          </div>

          <div id="feedback" class="text-sm font-semibold text-[var(--muted)]"></div>

          <div id="doneBox" class="hidden rounded-2xl border border-dashed border-brand-200 bg-brand-50/30 px-5 py-4 text-sm font-semibold text-brand-900">
            <p id="doneText"></p>
          </div>
        </div>
      </div>

      <div class="space-y-4 order-1 lg:order-2">
        <div class="rounded-2xl border border-[var(--border)] bg-[var(--card)] p-5 shadow-sm sticky top-2 z-20 lg:static">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-brand-100 text-brand-600">📊</span>
              <p class="text-sm font-semibold text-[var(--muted)]">{{ __('verbs.progress') }}</p>
            </div>
            <span id="progressPercent" class="rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold text-brand-700">0%</span>
          </div>
          <div class="mt-3 h-3 rounded-full bg-brand-100">
            <div id="progressBar" class="h-3 rounded-full bg-brand-600 transition-all duration-500" style="width:0%"></div>
          </div>
          <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
            <div class="rounded-2xl bg-green-50 px-4 py-3 text-green-700">
              <dt class="text-green-600">{{ __('verbs.correct') }}</dt>
              <dd id="correct" class="text-lg font-bold">0</dd>
            </div>
            <div class="rounded-2xl bg-red-50 px-4 py-3 text-red-700">
              <dt class="text-red-600">{{ __('verbs.wrong') }}</dt>
              <dd id="wrong" class="text-lg font-bold">0</dd>
            </div>
          </dl>
        </div>
        <div class="rounded-2xl border border-[var(--border)] bg-[var(--card)] p-5 shadow-sm text-sm text-[var(--muted)] space-y-2">
          <p class="font-bold text-[var(--fg)]">{{ __('verbs.how_it_works') }}</p>
          <ul class="list-disc pl-5 space-y-1">
            <li>{{ __('verbs.tip_start') }}</li>
            <li>{{ __('verbs.tip_modes') }}</li>
            <li>{{ __('verbs.tip_storage') }}</li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <div id="failureModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-[var(--bg)]/80 backdrop-blur-sm animate-fade"></div>
    <div class="relative mx-4 w-full max-w-md rounded-3xl border border-red-200 bg-[var(--card)] p-8 shadow-card space-y-4 animate-bounce-in">
      <div class="flex items-start gap-4">
        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-red-100 text-red-600">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4m0 4h.01M4.93 4.93l14.14 14.14"/><circle cx="12" cy="12" r="9"/></svg>
        </div>
        <div class="space-y-2">
          <p class="text-sm font-semibold text-red-600">{{ __('verbs.failed_title') }}</p>
          <p class="text-[var(--muted)]">{{ __('verbs.failed_message') }}</p>
        </div>
      </div>
      <div class="flex justify-end">
        <button id="retryBtn" class="inline-flex items-center gap-2 rounded-full bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md hover:bg-brand-700">
          {{ __('verbs.restart') }}
        </button>
      </div>
    </div>
  </div>

  <style>
    @keyframes fade-in-soft {
      0% { opacity: 0; }
      100% { opacity: 1; }
    }

    @keyframes bounce-in {
      0% { transform: scale(0.96); opacity: 0; }
      60% { transform: scale(1.03); opacity: 1; }
      100% { transform: scale(1); }
    }

    .animate-fade { animation: fade-in-soft 200ms ease; }
    .animate-bounce-in { animation: bounce-in 260ms ease; }

    @keyframes shake {
      0% { transform: translateX(0); }
      25% { transform: translateX(-4px); }
      50% { transform: translateX(4px); }
      75% { transform: translateX(-4px); }
      100% { transform: translateX(0); }
    }
    .shake {
      animation: shake 0.35s ease;
    }
  </style>

  <script>
    window.__VERBS__ = @json($verbs);
    @php
      $verbsI18n = [
          'form1' => __('verbs.form_label', ['number' => 1]),
          'form2' => __('verbs.form_label', ['number' => 2]),
          'form3' => __('verbs.form_label', ['number' => 3]),
          'form4' => __('verbs.form_label', ['number' => 4]),
          'askLabel' => __('verbs.ask_label', ['form' => ':form']),
          'startNeeded' => __('verbs.start_needed'),
          'completed' => __('verbs.completed'),
          'progressRestored' => __('verbs.progress_restored'),
          'revealed' => __('verbs.revealed', ['answer' => ':answer']),
          'correctAnswer' => __('verbs.correct_answer'),
          'wrongAnswer' => __('verbs.wrong_answer'),
          'noVerbs' => __('verbs.no_verbs'),
          'done' => __('verbs.done'),
          'result' => __('verbs.result'),
          'answerFor' => __('verbs.answer_for'),
          'settings_show' => __('verbs.settings_show'),
          'settings_hide' => __('verbs.settings_hide'),
      ];
    @endphp
    window.__VERBS_I18N__ = @json($verbsI18n);
  </script>
  <script>
    (function () {
      const verbs = Array.isArray(window.__VERBS__) ? window.__VERBS__ : [];
      const i18n = window.__VERBS_I18N__ || {};
      const storageKey = 'verbs_test_state_v1';
      const signature = createSignature(verbs);
      const choiceSuccessClasses = ['border-success', 'bg-success/10', 'text-success'];
      const choiceErrorClasses = ['border-destructive', 'bg-destructive/10', 'text-destructive'];
      const suggestionsByForm = buildSuggestionsByForm(verbs);
      const maxMistakes = 3;

      const els = {
          startBtn: document.getElementById('startBtn'),
          restartBtn: document.getElementById('restartBtn'),
          checkBtn: document.getElementById('checkBtn'),
          revealBtn: document.getElementById('revealBtn'),
          nextBtn: document.getElementById('nextBtn'),
          controlButtons: document.getElementById('controlButtons'),
          modeButtons: document.querySelectorAll('[data-mode-button]'),
          askButtons: document.querySelectorAll('[data-ask-button]'),
          showUk: document.getElementById('showUk'),
          settingsToggle: document.getElementById('settingsToggle'),
          settingsToggleText: document.getElementById('settingsToggleText'),
          settingsBody: document.getElementById('settingsBody'),
          baseVerb: document.getElementById('baseVerb'),
          ukVerb: document.getElementById('ukVerb'),
          askLabel: document.getElementById('askLabel'),
          hint: document.getElementById('hint'),
          typingBox: document.getElementById('typingBox'),
          choiceBox: document.getElementById('choiceBox'),
          answerInput: document.getElementById('answerInput'),
          suggestionsBox: document.getElementById('suggestionsBox'),
          progressText: document.getElementById('progressText'),
          progressBar: document.getElementById('progressBar'),
          progressPercent: document.getElementById('progressPercent'),
          correct: document.getElementById('correct'),
          wrong: document.getElementById('wrong'),
          feedback: document.getElementById('feedback'),
          doneBox: document.getElementById('doneBox'),
          doneText: document.getElementById('doneText'),
          questionCard: document.getElementById('questionCard'),
          failureModal: document.getElementById('failureModal'),
          retryBtn: document.getElementById('retryBtn'),
      };

      const defaultState = () => ({
          settings: {
              mode: 'hard',
              askWhat: 'random',
              showTranslation: false,
              settingsCollapsed: false,
          },
          queue: [],
          pos: 0,
          correct: 0,
          wrong: 0,
          failed: false,
          current: null,
          signature,
      });

      let state = defaultState();

      function buildSuggestionsByForm(list) {
          const map = { f1: new Set(), f2: new Set(), f3: new Set(), f4: new Set() };
          list.forEach((verb) => {
              if (verb.f1) map.f1.add(verb.f1);
              (verb.f2 || []).forEach((v) => map.f2.add(v));
              (verb.f3 || []).forEach((v) => map.f3.add(v));
              if (verb.f4) map.f4.add(verb.f4);
          });
          return {
              f1: Array.from(map.f1),
              f2: Array.from(map.f2),
              f3: Array.from(map.f3),
              f4: Array.from(map.f4),
          };
      }

      function createSignature(list) {
          return list
              .map((verb) => {
                  const f2 = Array.isArray(verb.f2) ? verb.f2.join(',') : '';
                  const f3 = Array.isArray(verb.f3) ? verb.f3.join(',') : '';
                  return `${verb.base}|${verb.translation}|${verb.f1}|${verb.f4}|${f2}|${f3}`;
              })
              .join(';');
      }

      function normalize(str) {
          return (str || '').toString().trim().toLowerCase();
      }

      function shuffle(array) {
          const arr = [...array];
          for (let i = arr.length - 1; i > 0; i -= 1) {
              const j = Math.floor(Math.random() * (i + 1));
              [arr[i], arr[j]] = [arr[j], arr[i]];
          }
          return arr;
      }

      function saveState() {
          if (!window.localStorage) return;
          const payload = {
              ...state,
              current: state.current
                  ? {
                      ...state.current,
                      answers: Array.isArray(state.current.answers) ? [...state.current.answers] : [],
                      answersRaw: Array.isArray(state.current.answersRaw) ? [...state.current.answersRaw] : [],
                      options: Array.isArray(state.current.options) ? [...state.current.options] : [],
                      selected: state.current.selected || null,
                  }
                  : null,
          };
          window.localStorage.setItem(storageKey, JSON.stringify(payload));
      }

      function loadState() {
          if (!window.localStorage) return null;
          const raw = window.localStorage.getItem(storageKey);
          if (!raw) return null;
          try {
              const parsed = JSON.parse(raw);
              if (parsed.signature !== signature) return null;
              if (!Array.isArray(parsed.queue) || !parsed.queue.length) return null;
              const cleanedQueue = parsed.queue.filter((i) => Number.isInteger(i) && i >= 0 && i < verbs.length);
              if (!cleanedQueue.length) return null;
              const settings = {
                  ...defaultState().settings,
                  ...(parsed.settings || {}),
              };
              const current = parsed.current && typeof parsed.current === 'object'
                  ? {
                      verbIndex: parsed.current.verbIndex,
                      askKey: parsed.current.askKey,
                      answers: Array.isArray(parsed.current.answers) ? parsed.current.answers : [],
                      answersRaw: Array.isArray(parsed.current.answersRaw) ? parsed.current.answersRaw : [],
                      answered: Boolean(parsed.current.answered),
                      wasCorrect: Boolean(parsed.current.wasCorrect),
                      options: Array.isArray(parsed.current.options) ? parsed.current.options : [],
                      selected: parsed.current.selected || null,
                  }
                  : null;

              return {
                  ...defaultState(),
                  ...parsed,
                  settings,
                  queue: cleanedQueue,
                  pos: Math.min(Math.max(parsed.pos || 0, 0), cleanedQueue.length),
                  current,
                  signature,
              };
          } catch (e) {
              return null;
          }
      }

      function readSettingsFromControls() {
          const modeButton = Array.from(els.modeButtons || []).find((btn) => btn.classList.contains('active'));
          const askButton = Array.from(els.askButtons || []).find((btn) => btn.classList.contains('active'));
          return {
              mode: modeButton?.value || 'hard',
              askWhat: askButton?.value || 'random',
              showTranslation: Boolean(els.showUk?.checked),
              settingsCollapsed: state.settings?.settingsCollapsed ?? false,
          };
      }

      function applySettingsToControls(settings) {
          Array.from(els.modeButtons || []).forEach((btn) => {
              const isActive = btn.value === settings.mode;
              btn.classList.toggle('active', isActive);
              btn.classList.toggle('border-primary', isActive);
              btn.classList.toggle('bg-primary/10', isActive);
          });
          Array.from(els.askButtons || []).forEach((btn) => {
              const isActive = btn.value === settings.askWhat;
              btn.classList.toggle('active', isActive);
              btn.classList.toggle('border-primary', isActive);
              btn.classList.toggle('bg-primary/10', isActive);
          });
          if (els.showUk) els.showUk.checked = settings.showTranslation;
          toggleSettingsBody(Boolean(settings.settingsCollapsed));
      }

      function toggleModeVisibility(mode) {
          if (!els.typingBox || !els.choiceBox) return;
          if (mode === 'easy') {
              els.typingBox.classList.add('hidden');
              els.choiceBox.classList.remove('hidden');
              hideSuggestions();
          } else {
              els.typingBox.classList.remove('hidden');
              els.choiceBox.classList.add('hidden');
              hideSuggestions();
          }
          if (els.controlButtons) {
              els.controlButtons.classList.toggle('hidden', mode === 'easy');
          }
      }

      function setFeedback(message, isPositive = false) {
          if (!els.feedback) return;
          els.feedback.textContent = message || '';
          els.feedback.classList.remove('text-success', 'text-destructive');
          if (message) {
              els.feedback.classList.add(isPositive ? 'text-success' : 'text-destructive');
          }
      }

      function updateProgress() {
          if (!els.progressText || !els.progressBar || !els.progressPercent) return;
          const total = state.queue.length;
          const currentIndex = Math.min(state.pos + 1, total);
          els.progressText.textContent = `${total ? currentIndex : 0} / ${total}`;
          const percent = total ? Math.round((state.pos / total) * 100) : 0;
          els.progressBar.style.width = `${percent}%`;
          els.progressPercent.textContent = `${percent}%`;
      }

      function updateStats() {
          if (els.correct) els.correct.textContent = state.correct;
          if (els.wrong) els.wrong.textContent = state.wrong;
      }

      function hintForVerb(verb) {
          if (!verb) return '';
          const base = Array.isArray(verb.base_forms) && verb.base_forms.length ? verb.base_forms.join(' / ') : verb.base;
          const f2 = Array.isArray(verb.f2) ? verb.f2.join(' / ') : verb.f2;
          const f3 = Array.isArray(verb.f3) ? verb.f3.join(' / ') : verb.f3;
          return `Base: ${base} • f1: ${verb.f1} • f2: ${f2} • f3: ${f3} • f4: ${verb.f4}`;
      }

      function labelForAskKey(key) {
          return {
              f1: i18n.form1 || 'Form 1',
              f2: i18n.form2 || 'Form 2',
              f3: i18n.form3 || 'Form 3',
              f4: i18n.form4 || 'Form 4',
          }[key] || key;
      }

      function resolveAskKey() {
          if (state.settings.askWhat === 'random') {
              const options = ['f1', 'f2', 'f3', 'f4'];
              return options[Math.floor(Math.random() * options.length)];
          }
          return state.settings.askWhat;
      }

      function getAnswersForVerb(verb, askKey) {
          if (!verb) return [];
          switch (askKey) {
              case 'f1':
                  return verb.f1 ? [verb.f1] : [];
              case 'f2':
                  return Array.isArray(verb.f2) ? verb.f2 : [];
              case 'f3':
                  return Array.isArray(verb.f3) ? verb.f3 : [];
              case 'f4':
                  return verb.f4 ? [verb.f4] : [];
              default:
                  return [];
          }
      }

      function createQueue() {
          const baseIndexes = verbs.map((_, idx) => idx);
          return shuffle(baseIndexes);
      }

      function setTranslationVisibility() {
          if (!els.ukVerb) return;
          els.ukVerb.classList.toggle('hidden', !state.settings.showTranslation);
      }

      function formatAskLabel(askKey) {
          const formLabel = labelForAskKey(askKey);
          return (i18n.askLabel || ':form').replace(':form', formLabel);
      }

      function setDone(message) {
          state.current = null;
          if (els.doneBox) els.doneBox.classList.remove('hidden');
          if (els.doneText) els.doneText.textContent = message;
          setFeedback('', true);
          updateProgress();
          saveState();
      }

      function setQuestionVisibility(visible) {
          if (!els.questionCard) return;
          els.questionCard.classList.toggle('hidden', !visible);
      }

      function toggleFailureModal(show) {
          if (!els.failureModal) return;
          els.failureModal.classList.toggle('hidden', !show);
          if (show) {
              els.failureModal.classList.add('flex', 'items-center', 'justify-center');
              const panel = els.failureModal.querySelector('.animate-bounce-in');
              const backdrop = els.failureModal.querySelector('.animate-fade');
              if (panel) {
                  panel.classList.remove('animate-bounce-in');
                  void panel.offsetWidth;
                  panel.classList.add('animate-bounce-in');
              }
              if (backdrop) {
                  backdrop.classList.remove('animate-fade');
                  void backdrop.offsetWidth;
                  backdrop.classList.add('animate-fade');
              }
          } else {
              els.failureModal.classList.remove('flex', 'items-center', 'justify-center');
          }
      }

      function toggleSettingsBody(collapsed) {
          if (!els.settingsBody || !els.settingsToggleText) return;
          els.settingsBody.classList.toggle('hidden', collapsed);
          els.settingsToggleText.textContent = collapsed
              ? (i18n.settings_show || 'Show settings')
              : (i18n.settings_hide || 'Hide settings');
      }

      function hideSuggestions() {
          if (els.suggestionsBox) {
              els.suggestionsBox.classList.add('hidden');
              els.suggestionsBox.innerHTML = '';
          }
      }

      function renderSuggestions(askKey, query = '') {
          if (!els.suggestionsBox || state.settings.mode !== 'medium') {
              hideSuggestions();
              return;
          }
          const list = suggestionsByForm[askKey] || [];
          const normalizedQuery = normalize(query);
          const matches = list
              .filter((item) => !normalizedQuery || normalize(item).includes(normalizedQuery))
              .slice(0, 6);
          if (!matches.length) {
              hideSuggestions();
              return;
          }
          els.suggestionsBox.innerHTML = '';
          matches.forEach((text) => {
              const btn = document.createElement('button');
              btn.type = 'button';
              btn.className = 'w-full rounded-xl border border-[var(--border)] bg-[var(--card)] px-3 py-2 text-left text-sm font-semibold text-[var(--fg)] shadow-sm transition hover:-translate-y-0.5 hover:shadow hover:bg-brand-50';
              btn.textContent = text;
              btn.addEventListener('click', () => {
                  if (!els.answerInput) return;
                  els.answerInput.value = text;
                  els.answerInput.focus();
                  hideSuggestions();
              });
              els.suggestionsBox.appendChild(btn);
          });
          els.suggestionsBox.classList.remove('hidden');
      }

      function resetChoiceHighlights() {
          if (!els.choiceBox) return;
          Array.from(els.choiceBox.children).forEach((button) => {
              const btn = button;
              choiceSuccessClasses.forEach((cls) => btn.classList.remove(cls));
              choiceErrorClasses.forEach((cls) => btn.classList.remove(cls));
              btn.classList.remove('shake');
          });
      }

      function findChoiceButtonByValue(value) {
          if (!els.choiceBox) return null;
          return Array.from(els.choiceBox.children).find((button) => {
              const normalized = button.dataset.value || normalize(button.textContent || '');
              return normalized === value;
          }) || null;
      }

      function applyChoiceResult(isCorrect, chosenBtn) {
          resetChoiceHighlights();
          if (!chosenBtn) return;
          const target = chosenBtn;
          if (isCorrect) {
              choiceSuccessClasses.forEach((cls) => target.classList.add(cls));
          } else {
              choiceErrorClasses.forEach((cls) => target.classList.add(cls));
              target.classList.add('shake');
              target.addEventListener('animationend', () => target.classList.remove('shake'), { once: true });
          }
      }

      function renderChoices(options) {
          if (!els.choiceBox) return;
          els.choiceBox.innerHTML = '';
          options.forEach((option, idx) => {
              const btn = document.createElement('button');
              btn.type = 'button';
              btn.className = 'w-full rounded-2xl border border-[var(--border)] bg-[var(--card)] px-4 py-3 text-sm font-semibold text-[var(--fg)] shadow-sm transition hover:-translate-y-0.5 hover:shadow-md hover:border-brand-400';
              btn.textContent = option.label;
              btn.dataset.index = idx.toString();
              btn.dataset.value = option.normalized;
              btn.addEventListener('click', () => {
                  if (state.current?.answered) return;
                  evaluateAnswer(option.normalized, btn);
              });
              els.choiceBox.appendChild(btn);
          });
      }

      function buildChoiceOptions(verb, askKey, answers) {
          const correctRaw = getAnswersForVerb(verb, askKey)[0] || answers[0];
          const pool = verbs
              .map((item) => {
                  const value = getAnswersForVerb(item, askKey)[0];
                  return value || null;
              })
              .filter(Boolean)
              .map((value) => value);

          const uniquePool = Array.from(new Set(pool.filter((value) => !answers.includes(normalize(value)))));
          const distractors = shuffle(uniquePool).slice(0, 3);
          const optionValues = shuffle([correctRaw, ...distractors].filter(Boolean));

          return optionValues.map((value) => ({
              label: value,
              normalized: normalize(value),
              correct: answers.includes(normalize(value)),
          }));
      }

      function renderQuestion() {
          if (!verbs.length) {
              setFeedback(i18n.noVerbs || '');
              if (els.baseVerb) els.baseVerb.textContent = '—';
              if (els.ukVerb) els.ukVerb.textContent = '';
              setQuestionVisibility(false);
              toggleFailureModal(false);
              return;
          }

          if (state.pos >= state.queue.length) {
              const total = state.queue.length || 0;
              const resultText = `${i18n.completed || ''}. ${i18n.result || 'Result'}: ${state.correct}/${total}`;
              setDone(resultText);
              toggleFailureModal(false);
              return;
          }

          setQuestionVisibility(true);
          const verbIndex = state.queue[state.pos];
          const verb = verbs[verbIndex];
          const existingCurrent = state.current && state.current.verbIndex === verbIndex ? state.current : null;
          const askKey = existingCurrent?.askKey || resolveAskKey();
          const answersRaw = (existingCurrent?.answersRaw && existingCurrent.answersRaw.length
              ? existingCurrent.answersRaw
              : getAnswersForVerb(verb, askKey).map((a) => a || '').filter(Boolean)
          );
          const answersNormalized = answersRaw.map((a) => normalize(a));

          state.current = {
              verbIndex,
              askKey,
              answers: answersNormalized,
              answersRaw,
              answered: existingCurrent?.answered || false,
              wasCorrect: existingCurrent?.wasCorrect || false,
              options: [],
              selected: existingCurrent?.selected || null,
          };

          if (els.baseVerb) els.baseVerb.textContent = verb?.base || '—';
          if (els.ukVerb) els.ukVerb.textContent = verb?.translation || '';
          setTranslationVisibility();

          if (els.askLabel) els.askLabel.textContent = formatAskLabel(askKey);
          if (els.hint) els.hint.textContent = hintForVerb(verb);

          if (state.settings.mode !== 'easy' && els.answerInput) {
              els.answerInput.value = '';
              els.answerInput.focus();
              renderSuggestions(askKey, '');
          }

          toggleModeVisibility(state.settings.mode);
          setFeedback('');
          if (els.doneBox) els.doneBox.classList.add('hidden');

          if (state.settings.mode === 'easy') {
              const options = (existingCurrent?.options && existingCurrent.options.length)
                  ? existingCurrent.options
                  : buildChoiceOptions(verb, askKey, answersNormalized);
              state.current.options = options;
              renderChoices(options);
              resetChoiceHighlights();
          } else if (els.choiceBox) {
              els.choiceBox.innerHTML = '';
          }

          if (state.current.answered) {
              const reveal = (i18n.revealed || 'Correct answer: :answer').replace(
                  ':answer',
                  state.current.answersRaw?.[0] || state.current.answers?.[0] || ''
              );
              setFeedback(
                  state.current.wasCorrect ? (i18n.correctAnswer || 'Correct!') : `${i18n.wrongAnswer || 'Wrong'}. ${reveal}`,
                  state.current.wasCorrect
              );
              if (state.settings.mode === 'easy') {
                  const chosenBtn = state.current.selected
                      ? findChoiceButtonByValue(state.current.selected)
                      : null;
                  applyChoiceResult(state.current.wasCorrect, chosenBtn);
              }
          }

          updateProgress();
          updateStats();
          saveState();
      }

      function evaluateAnswer(rawAnswer, choiceBtn = null) {
          if (!state.current || state.current.answered) return;
          const answer = normalize(rawAnswer);
          const isCorrect = state.current.answers.includes(answer);
          state.current.answered = true;
          state.current.wasCorrect = isCorrect;
          if (choiceBtn) {
              state.current.selected = answer;
          }

          if (isCorrect) {
              state.correct += 1;
              setFeedback(i18n.correctAnswer || 'Correct!', true);
          } else {
              state.wrong += 1;
              const revealed = (i18n.revealed || 'Correct answer: :answer').replace(
                  ':answer',
                  state.current.answers[0] || ''
              );
              setFeedback(`${i18n.wrongAnswer || 'Wrong'}. ${revealed}`, false);
          }

          updateStats();
          if (state.settings.mode === 'easy') {
              const targetBtn = choiceBtn || (state.current.selected ? findChoiceButtonByValue(state.current.selected) : null);
              applyChoiceResult(isCorrect, targetBtn);
              setTimeout(() => nextQuestion(), 650);
          }
          if (state.wrong >= maxMistakes) {
              state.failed = true;
              setFeedback('');
              toggleFailureModal(true);
              setQuestionVisibility(false);
          }
          saveState();
      }

      function nextQuestion() {
          if (!state.queue.length) return;
          if (state.pos < state.queue.length) {
              state.pos += 1;
          }
          renderQuestion();
      }

      function revealAnswer() {
          if (!state.current) return;
          const revealed = state.current.answers[0] || '';
          if (state.settings.mode !== 'easy' && els.answerInput) {
              els.answerInput.value = revealed;
          }
          if (state.settings.mode === 'easy' && els.choiceBox) {
              resetChoiceHighlights();
              Array.from(els.choiceBox.children).forEach((button) => {
                  const btn = button;
                  const normalized = btn.dataset.value || normalize(btn.textContent || '');
                  if (state.current.answers.includes(normalized)) {
                      choiceSuccessClasses.forEach((cls) => btn.classList.add(cls));
                  }
              });
              if (state.current.selected) {
                  const chosenBtn = findChoiceButtonByValue(state.current.selected);
                  if (chosenBtn && !state.current.wasCorrect) {
                      choiceErrorClasses.forEach((cls) => chosenBtn.classList.add(cls));
                      chosenBtn.classList.add('shake');
                      chosenBtn.addEventListener('animationend', () => chosenBtn.classList.remove('shake'), { once: true });
                  }
              }
          }
          const message = (i18n.revealed || 'Correct answer: :answer').replace(':answer', revealed);
          setFeedback(message, true);
          saveState();
      }

      function startTest() {
          state = defaultState();
          state.settings = readSettingsFromControls();
          state.settings.settingsCollapsed = true;
          applySettingsToControls(state.settings);
          state.queue = createQueue();
          if (!state.queue.length) {
              setFeedback(i18n.noVerbs || '');
              return;
          }
          state.pos = 0;
          state.correct = 0;
          state.wrong = 0;
          state.failed = false;
          toggleFailureModal(false);
          setQuestionVisibility(true);
          renderQuestion();
      }

      function restoreState(saved) {
          state = {
              ...defaultState(),
              ...saved,
          };
          applySettingsToControls(state.settings);
          toggleModeVisibility(state.settings.mode);
          setQuestionVisibility(true);
          renderQuestion();
          if (i18n.progressRestored) {
              setFeedback(i18n.progressRestored, true);
          }
      }

      function bindEvents() {
          els.startBtn?.addEventListener('click', () => startTest());
          els.restartBtn?.addEventListener('click', () => startTest());
          els.retryBtn?.addEventListener('click', () => startTest());
          els.settingsToggle?.addEventListener('click', () => {
              state.settings.settingsCollapsed = !state.settings.settingsCollapsed;
              toggleSettingsBody(state.settings.settingsCollapsed);
              saveState();
          });
          els.checkBtn?.addEventListener('click', () => {
              if (state.settings.mode === 'easy') return;
              evaluateAnswer(els.answerInput?.value || '');
          });
          els.nextBtn?.addEventListener('click', () => nextQuestion());
          els.revealBtn?.addEventListener('click', () => revealAnswer());
          Array.from(els.modeButtons || []).forEach((btn) => {
              btn.addEventListener('click', () => {
                  state.settings.mode = btn.value;
                  applySettingsToControls(state.settings);
                  toggleModeVisibility(state.settings.mode);
                  saveState();
              });
          });
          Array.from(els.askButtons || []).forEach((btn) => {
              btn.addEventListener('click', () => {
                  state.settings.askWhat = btn.value;
                  applySettingsToControls(state.settings);
                  saveState();
              });
          });
          els.showUk?.addEventListener('change', () => {
              state.settings.showTranslation = Boolean(els.showUk.checked);
              setTranslationVisibility();
              saveState();
          });
          els.answerInput?.addEventListener('keydown', (event) => {
              if (event.key === 'Enter') {
                  event.preventDefault();
                  evaluateAnswer(els.answerInput.value);
              }
          });
          els.answerInput?.addEventListener('input', (event) => {
              const value = event.target.value || '';
              const askKey = state.current?.askKey || 'f1';
              renderSuggestions(askKey, value);
          });
          els.answerInput?.addEventListener('focus', (event) => {
              const askKey = state.current?.askKey || 'f1';
              renderSuggestions(askKey, event.target.value || '');
          });
          els.answerInput?.addEventListener('blur', () => {
              setTimeout(() => hideSuggestions(), 150);
          });
      }

      function init() {
          if (!els.baseVerb) return;
          bindEvents();

          if (!verbs.length) {
              setFeedback(i18n.noVerbs || '');
              return;
          }

          const savedState = loadState();
          if (savedState) {
              restoreState(savedState);
          } else {
              toggleModeVisibility(state.settings.mode);
              applySettingsToControls(state.settings);
              setFeedback(i18n.startNeeded || '');
              setQuestionVisibility(false);
              toggleFailureModal(false);
              updateProgress();
              updateStats();
          }
      }

      document.addEventListener('DOMContentLoaded', init);
    })();
  </script>
@endsection
