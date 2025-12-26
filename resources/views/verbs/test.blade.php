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
    <h2 class="text-2xl font-semibold text-foreground mb-2">{{ __('verbs_test.done') }}</h2>
    <p id="doneText" class="text-lg text-muted-foreground"></p>
  </div>

  @endif
</div>

<script>
  // Pass verbs data to JavaScript
  window.__VERBS__ = @json($verbs);
</script>

@vite(['resources/js/verbs-test.js'])
@endsection
