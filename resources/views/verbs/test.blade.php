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
</script>

@vite(['resources/js/verbs-test.js'])
@endsection
