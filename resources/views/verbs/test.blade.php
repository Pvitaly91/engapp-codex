@extends('layouts.engram')

@section('title', __('verbs.title'))

@section('content')
  <div class="space-y-8">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
      <div class="space-y-2">
        <p class="text-sm text-muted-foreground">{{ __('verbs.subtitle') }}</p>
        <h1 class="text-3xl font-semibold text-foreground">{{ __('verbs.title') }}</h1>
        <p class="text-muted-foreground max-w-3xl">{{ __('verbs.description') }}</p>
      </div>
      <div class="flex flex-wrap gap-3">
        <button id="startBtn" class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground shadow transition hover:-translate-y-0.5 hover:shadow-lg">
          {{ __('verbs.start') }}
        </button>
        <button id="restartBtn" class="inline-flex items-center gap-2 rounded-xl border border-border bg-background px-4 py-2 text-sm font-semibold text-foreground shadow-sm transition hover:-translate-y-0.5 hover:shadow">
          {{ __('verbs.restart') }}
        </button>
      </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-[1.5fr_0.9fr]">
      <div class="space-y-4">
        <div class="rounded-2xl border border-border/70 bg-card p-5 shadow-soft">
          <div class="flex items-center justify-between gap-3">
            <h2 class="text-lg font-semibold text-foreground">{{ __('verbs.settings') }}</h2>
            <span class="rounded-full bg-muted px-3 py-1 text-xs font-semibold text-muted-foreground" id="verbs-count-badge">{{ count($verbs) }} {{ __('verbs.verbs_total') }}</span>
          </div>
          <div class="mt-4 grid gap-4 md:grid-cols-2">
            <div class="space-y-2">
              <label for="mode" class="text-sm font-semibold text-muted-foreground">{{ __('verbs.mode') }}</label>
              <select id="mode" class="w-full rounded-xl border border-border bg-background px-3 py-2 text-sm font-medium text-foreground shadow-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/15">
                <option value="typing">{{ __('verbs.mode_typing') }}</option>
                <option value="choice">{{ __('verbs.mode_choice') }}</option>
              </select>
            </div>
            <div class="space-y-2">
              <label for="askWhat" class="text-sm font-semibold text-muted-foreground">{{ __('verbs.ask_what') }}</label>
              <select id="askWhat" class="w-full rounded-xl border border-border bg-background px-3 py-2 text-sm font-medium text-foreground shadow-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/15">
                <option value="random">{{ __('verbs.ask_random') }}</option>
                <option value="f1">{{ __('verbs.ask_f1') }}</option>
                <option value="f2">{{ __('verbs.ask_f2') }}</option>
                <option value="f3">{{ __('verbs.ask_f3') }}</option>
                <option value="f4">{{ __('verbs.ask_f4') }}</option>
              </select>
            </div>
            <div class="space-y-2">
              <label for="count" class="text-sm font-semibold text-muted-foreground">{{ __('verbs.count') }}</label>
              <input id="count" type="number" min="1" step="1" value="10" class="w-full rounded-xl border border-border bg-background px-3 py-2 text-sm font-medium text-foreground shadow-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/15" />
            </div>
            <div class="flex items-center gap-3 rounded-xl border border-border/70 bg-muted/40 px-3 py-2">
              <input id="showUk" type="checkbox" class="h-4 w-4 rounded border-border text-primary focus:ring-primary" />
              <label for="showUk" class="text-sm font-semibold text-foreground">{{ __('verbs.show_translation') }}</label>
            </div>
          </div>
        </div>

        <div class="rounded-2xl border border-border/70 bg-card p-6 shadow-soft space-y-4">
          <div class="flex items-center justify-between gap-3">
            <div>
              <p class="text-xs uppercase tracking-[0.16em] text-muted-foreground">{{ __('verbs.question') }}</p>
              <p id="askLabel" class="text-sm font-semibold text-foreground"></p>
            </div>
            <div class="text-sm text-muted-foreground">
              <span id="progressText">0 / 0</span>
            </div>
          </div>

          <div class="space-y-3 rounded-xl border border-border/70 bg-muted/40 px-4 py-3">
            <p class="text-sm text-muted-foreground">{{ __('verbs.answer_for') }}</p>
            <div class="flex flex-col gap-1">
              <div id="baseVerb" class="text-3xl font-semibold text-foreground">—</div>
              <div id="ukVerb" class="text-sm text-muted-foreground"></div>
            </div>
          </div>

          <div class="rounded-xl border border-border/70 bg-background px-4 py-3">
            <p class="text-xs uppercase tracking-[0.12em] text-muted-foreground">{{ __('verbs.hint') }}</p>
            <p id="hint" class="mt-1 text-sm text-foreground"></p>
          </div>

          <div id="typingBox" class="space-y-3">
            <label for="answerInput" class="text-sm font-semibold text-muted-foreground">{{ __('verbs.type_answer') }}</label>
            <input id="answerInput" type="text" autocomplete="off" class="w-full rounded-xl border border-border/70 bg-background px-4 py-3 text-lg font-semibold text-foreground shadow-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20" />
          </div>

          <div id="choiceBox" class="grid gap-3 md:grid-cols-2"></div>

          <div class="flex flex-wrap items-center gap-3">
            <button id="checkBtn" class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground shadow transition hover:-translate-y-0.5 hover:shadow">
              {{ __('verbs.check') }}
            </button>
            <button id="revealBtn" class="inline-flex items-center gap-2 rounded-xl border border-border bg-background px-4 py-2 text-sm font-semibold text-foreground shadow-sm transition hover:-translate-y-0.5 hover:shadow">
              {{ __('verbs.reveal') }}
            </button>
            <button id="nextBtn" class="inline-flex items-center gap-2 rounded-xl border border-border bg-muted px-4 py-2 text-sm font-semibold text-foreground shadow-sm transition hover:-translate-y-0.5 hover:shadow">
              {{ __('verbs.next') }}
            </button>
          </div>

          <div id="feedback" class="text-sm font-semibold text-muted-foreground"></div>

          <div id="doneBox" class="hidden rounded-xl border border-border/70 bg-muted/50 px-4 py-3 text-sm font-semibold text-foreground">
            <p id="doneText"></p>
          </div>
        </div>
      </div>

      <div class="space-y-4">
        <div class="rounded-2xl border border-border/70 bg-card p-5 shadow-soft">
          <div class="flex items-center justify-between">
            <p class="text-sm font-semibold text-muted-foreground">{{ __('verbs.progress') }}</p>
            <span id="progressPercent" class="text-sm font-semibold text-muted-foreground">0%</span>
          </div>
          <div class="mt-3 h-3 rounded-full bg-muted">
            <div id="progressBar" class="h-3 rounded-full bg-primary transition-all duration-500" style="width:0%"></div>
          </div>
          <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
            <div class="rounded-xl bg-success/10 px-3 py-2 text-success">
              <dt>{{ __('verbs.correct') }}</dt>
              <dd id="correct" class="text-lg font-semibold">0</dd>
            </div>
            <div class="rounded-xl bg-destructive/10 px-3 py-2 text-destructive">
              <dt>{{ __('verbs.wrong') }}</dt>
              <dd id="wrong" class="text-lg font-semibold">0</dd>
            </div>
          </dl>
        </div>
        <div class="rounded-2xl border border-border/70 bg-card p-5 shadow-soft text-sm text-muted-foreground space-y-2">
          <p class="font-semibold text-foreground">{{ __('verbs.how_it_works') }}</p>
          <ul class="list-disc pl-5 space-y-1">
            <li>{{ __('verbs.tip_start') }}</li>
            <li>{{ __('verbs.tip_modes') }}</li>
            <li>{{ __('verbs.tip_storage') }}</li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <script>
    window.__VERBS__ = @json($verbs);
    window.__VERBS_I18N__ = @json([
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
    ]);
  </script>
  @vite('resources/js/verbs-test.js')
@endsection
