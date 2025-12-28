@extends('layouts.engram')

@section('title', __('public.home.title'))

@section('content')
<div class="space-y-20">
  <!-- HERO -->
  <section id="hero" data-animate class="relative overflow-hidden rounded-[2.5rem] border border-border/80 bg-gradient-to-br from-primary/10 via-background to-secondary/10 p-10 shadow-soft md:p-14">
    <div class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_20%_15%,hsla(var(--accent),0.12),transparent_25%),radial-gradient(circle_at_80%_0%,hsla(var(--primary),0.14),transparent_30%)]"></div>
    <div class="grid gap-12 md:grid-cols-[1.35fr_1fr]">
      <div class="space-y-8" data-animate data-animate-delay="120">
        <span class="inline-flex items-center gap-2 rounded-full bg-background/70 px-5 py-1.5 text-xs font-semibold uppercase tracking-[0.4em] text-primary backdrop-blur">
          {{ __('public.home.badge') }}
        </span>
        <div class="space-y-5">
          <h1 class="text-4xl font-bold tracking-tight text-foreground md:text-6xl">
            {{ __('public.home.hero_title') }} <span class="bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">{{ __('public.home.hero_title_accent') }}</span>
          </h1>
          <p class="text-base leading-relaxed text-muted-foreground md:text-xl max-w-2xl">
            {{ __('public.home.hero_description') }}
          </p>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
          <a href="{{ route('catalog.tests-cards') }}" class="group inline-flex items-center justify-center gap-2 rounded-2xl bg-primary px-7 py-3.5 text-sm font-semibold text-primary-foreground shadow-lg transition hover:-translate-y-0.5 hover:shadow-xl">
            {{ __('public.home.to_catalog') }}
            <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </a>
          <a href="{{ route('theory.index') }}" class="group inline-flex items-center justify-center gap-2 rounded-2xl border border-border bg-background/80 px-7 py-3.5 text-sm font-semibold text-foreground backdrop-blur transition hover:border-primary hover:text-primary">
            {{ __('public.home.go_to_theory') }}
            <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </a>
        </div>
        <div class="grid gap-4 sm:grid-cols-3">
          @php
            $stats = [
              ['label' => __('public.home.stat_categories'), 'value' => '120+'],
              ['label' => __('public.home.stat_ai_hints'), 'value' => '2 400+'],
              ['label' => __('public.home.stat_tags'), 'value' => '7 500+'],
            ];
          @endphp
          @foreach ($stats as $stat)
            <div class="rounded-2xl border border-border/70 bg-card/90 p-4 shadow-sm">
              <p class="text-2xl font-bold text-primary">{{ $stat['value'] }}</p>
              <p class="text-sm text-muted-foreground">{{ $stat['label'] }}</p>
            </div>
          @endforeach
        </div>
      </div>

      <div class="space-y-6 rounded-3xl border border-border/60 bg-card/90 p-6 shadow-xl backdrop-blur" data-animate data-animate-delay="200">
        <div class="space-y-3">
          <p class="text-xs font-semibold uppercase tracking-[0.3em] text-primary">{{ __('public.home.whats_changed') }}</p>
          <h2 class="text-2xl font-semibold text-foreground">{{ __('public.home.new_layout') }}</h2>
          <p class="text-sm leading-relaxed text-muted-foreground">{{ __('public.home.new_layout_desc') }}</p>
        </div>
        <dl class="space-y-3 text-sm text-muted-foreground">
          <div class="flex items-start gap-3 rounded-2xl border border-dashed border-primary/40 bg-primary/5 p-4">
            <span class="mt-1 h-2.5 w-2.5 rounded-full bg-primary"></span>
            <div>
              <dt class="font-semibold text-foreground">{{ __('public.home.single_frame') }}</dt>
              <dd>{{ __('public.home.single_frame_desc') }}</dd>
            </div>
          </div>
          <div class="flex items-start gap-3 rounded-2xl border border-border/80 bg-background/80 p-4">
            <span class="mt-1 h-2.5 w-2.5 rounded-full bg-secondary"></span>
            <div>
              <dt class="font-semibold text-foreground">{{ __('public.home.visible_cta') }}</dt>
              <dd>{{ __('public.home.visible_cta_desc') }}</dd>
            </div>
          </div>
          <div class="flex items-start gap-3 rounded-2xl border border-border/80 bg-background/80 p-4">
            <span class="mt-1 h-2.5 w-2.5 rounded-full bg-accent"></span>
            <div>
              <dt class="font-semibold text-foreground">{{ __('public.home.dark_theme') }}</dt>
              <dd>{{ __('public.home.dark_theme_desc') }}</dd>
            </div>
          </div>
        </dl>
      </div>
    </div>
  </section>

  <!-- PLATFORM MAP -->
  @php
    $pillars = [
      [
        'title' => __('public.home.pillar_catalog_title'),
        'description' => __('public.home.pillar_catalog_desc'),
        'link' => route('catalog.tests-cards'),
        'accent' => 'primary',
        'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
      ],
      [
        'title' => __('public.home.pillar_theory_title'),
        'description' => __('public.home.pillar_theory_desc'),
        'link' => route('theory.index'),
        'accent' => 'secondary',
        'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13',
      ],
      [
        'title' => __('public.home.pillar_words_title'),
        'description' => __('public.home.pillar_words_desc'),
        'link' => route('words.test'),
        'accent' => 'accent',
        'icon' => 'M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129',
      ],
      [
        'title' => __('public.home.pillar_verbs_title'),
        'description' => __('public.home.pillar_verbs_desc'),
        'link' => route('verbs.test'),
        'accent' => 'success',
        'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
      ],
    ];
  @endphp
  <section id="solutions" class="space-y-8" data-animate>
    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between" data-animate data-animate-delay="80">
      <div class="space-y-2">
        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-primary">{{ __('public.home.product_map') }}</p>
        <h2 class="text-3xl font-bold text-foreground md:text-4xl">{{ __('public.home.public_modules') }}</h2>
        <p class="max-w-2xl text-base leading-relaxed text-muted-foreground">{{ __('public.home.public_modules_desc') }}</p>
      </div>
    </div>
    <div class="grid gap-6 md:grid-cols-2" data-animate data-animate-delay="160">
      @foreach ($pillars as $card)
        <article class="group relative flex h-full flex-col justify-between overflow-hidden rounded-3xl border border-border/70 bg-card p-8 shadow-soft transition hover:-translate-y-1.5 hover:border-{{ $card['accent'] }}/60 hover:shadow-xl">
          <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-{{ $card['accent'] }}/10 transition group-hover:scale-150"></div>
          <div class="relative space-y-5">
            <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-{{ $card['accent'] }}/10 text-{{ $card['accent'] }} ring-1 ring-{{ $card['accent'] }}/20">
              <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}" />
              </svg>
            </span>
            <h3 class="text-xl font-semibold text-foreground">{{ $card['title'] }}</h3>
            <p class="text-sm leading-relaxed text-muted-foreground">{{ $card['description'] }}</p>
          </div>
          <a href="{{ $card['link'] }}" class="relative mt-6 inline-flex items-center gap-2 text-sm font-semibold text-{{ $card['accent'] }} transition group-hover:gap-3">
            {{ __('public.common.go_to') }}
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </a>
        </article>
      @endforeach
    </div>
  </section>

  <!-- EXPERIENCE FLOW -->
  <section id="team-collaboration" class="space-y-8" data-animate>
    <div class="flex flex-col gap-2">
      <p class="text-xs font-semibold uppercase tracking-[0.35em] text-primary">{{ __('public.home.team_processes') }}</p>
      <h2 class="text-3xl font-bold text-foreground md:text-4xl">{{ __('public.home.public_experience') }}</h2>
      <p class="max-w-3xl text-base leading-relaxed text-muted-foreground">{{ __('public.home.public_experience_desc') }}</p>
    </div>
    <div class="grid gap-5 md:grid-cols-3" data-animate data-animate-delay="100">
      @php
        $steps = [
          ['title' => __('public.home.step_find'), 'body' => __('public.home.step_find_desc'), 'icon' => 'M8 16l-4-4m0 0l4-4m-4 4h18'],
          ['title' => __('public.home.step_start'), 'body' => __('public.home.step_start_desc'), 'icon' => 'M12 4v16m8-8H4'],
          ['title' => __('public.home.step_share'), 'body' => __('public.home.step_share_desc'), 'icon' => 'M5 13l4 4L19 7'],
        ];
      @endphp
      @foreach ($steps as $step)
        <div class="rounded-3xl border border-border/70 bg-card/90 p-6 shadow-soft">
          <div class="flex items-center gap-3">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-primary/10 text-primary ring-1 ring-primary/20">
              <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $step['icon'] }}" />
              </svg>
            </span>
            <h3 class="text-lg font-semibold text-foreground">{{ $step['title'] }}</h3>
          </div>
          <p class="mt-3 text-sm leading-relaxed text-muted-foreground">{{ $step['body'] }}</p>
        </div>
      @endforeach
    </div>
  </section>

  <!-- AI Toolkit -->
  <section id="ai-toolkit" class="space-y-8" data-animate>
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
      <div>
        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-primary">{{ __('public.home.ai_toolkit_section') }}</p>
        <h2 class="text-3xl font-bold text-foreground md:text-4xl">{{ __('public.home.ai_hints_highlighted') }}</h2>
        <p class="max-w-2xl text-base leading-relaxed text-muted-foreground">{{ __('public.home.ai_hints_desc') }}</p>
      </div>
      <a href="{{ route('catalog.tests-cards') }}" class="inline-flex items-center gap-2 rounded-full border border-border px-5 py-2 text-sm font-semibold text-foreground transition hover:border-primary hover:text-primary">{{ __('public.home.view_catalog') }}</a>
    </div>
    <div class="grid gap-6 md:grid-cols-[1.1fr_1fr]">
      <div class="rounded-3xl border border-border/70 bg-card p-6 shadow-soft space-y-4">
        <div class="inline-flex items-center gap-2 rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">{{ __('public.home.ai_catalog_badge') }}</div>
        <p class="text-lg font-semibold text-foreground">{{ __('public.home.ai_highlight_title') }}</p>
        <p class="text-sm leading-relaxed text-muted-foreground">{{ __('public.home.ai_highlight_desc') }}</p>
        <ul class="space-y-2 text-sm text-muted-foreground">
          <li class="flex items-start gap-2"><span class="mt-1 h-1.5 w-1.5 rounded-full bg-primary"></span>{{ __('public.home.ai_point1') }}</li>
          <li class="flex items-start gap-2"><span class="mt-1 h-1.5 w-1.5 rounded-full bg-secondary"></span>{{ __('public.home.ai_point2') }}</li>
          <li class="flex items-start gap-2"><span class="mt-1 h-1.5 w-1.5 rounded-full bg-accent"></span>{{ __('public.home.ai_point3') }}</li>
        </ul>
      </div>
      <div class="rounded-3xl border border-dashed border-primary/40 bg-primary/5 p-6 shadow-inner space-y-4">
        <div class="flex items-center gap-3">
          <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-primary text-white">⚡</span>
          <div>
            <p class="text-sm font-semibold text-primary">{{ __('public.home.unified_experience') }}</p>
            <p class="text-xs text-muted-foreground">{{ __('public.home.unified_experience_desc') }}</p>
          </div>
        </div>
        <div class="grid gap-3 sm:grid-cols-2">
          <div class="rounded-2xl border border-border bg-background/80 p-4 text-sm">
            <p class="font-semibold text-foreground">{{ __('public.home.quick_start_title') }}</p>
            <p class="mt-1 text-muted-foreground">{{ __('public.home.quick_start_desc') }}</p>
          </div>
          <div class="rounded-2xl border border-border bg-background/80 p-4 text-sm">
            <p class="font-semibold text-foreground">{{ __('public.home.visible_structure_title') }}</p>
            <p class="mt-1 text-muted-foreground">{{ __('public.home.visible_structure_desc') }}</p>
          </div>
        </div>
        <div class="rounded-2xl border border-border bg-background/90 p-4 text-sm">
          <p class="font-semibold text-foreground">{{ __('public.home.team_control_title') }}</p>
          <p class="mt-1 text-muted-foreground">{{ __('public.home.team_control_desc') }}</p>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA -->
  <section class="relative overflow-hidden rounded-[2rem] border border-border bg-gradient-to-r from-primary/15 via-background to-secondary/10 p-10 shadow-soft" data-animate>
    <div class="absolute -left-10 top-0 h-52 w-52 rounded-full bg-primary/15 blur-3xl"></div>
    <div class="absolute right-4 -bottom-14 h-48 w-48 rounded-full bg-secondary/15 blur-3xl"></div>
    <div class="relative grid gap-8 md:grid-cols-[1.4fr_1fr] md:items-center">
      <div class="space-y-4">
        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-primary">{{ __('public.home.ready_to_work') }}</p>
        <h2 class="text-3xl font-bold text-foreground md:text-4xl">{{ __('public.home.try_updated') }}</h2>
        <p class="text-base leading-relaxed text-muted-foreground max-w-2xl">{{ __('public.home.try_updated_desc') }}</p>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
          <a href="{{ route('catalog.tests-cards') }}" class="inline-flex items-center justify-center gap-2 rounded-full bg-primary px-6 py-3 text-sm font-semibold text-white shadow-lg transition hover:-translate-y-0.5 hover:shadow-xl">{{ __('public.home.open_catalog') }}</a>
          <a href="{{ route('pages.index') }}" class="inline-flex items-center justify-center gap-2 rounded-full border border-border px-6 py-3 text-sm font-semibold text-foreground transition hover:border-primary hover:text-primary">{{ __('public.home.go_to_theory') }}</a>
        </div>
      </div>
      <div class="relative rounded-2xl border border-border/80 bg-card/90 p-6 shadow-lg">
        <div class="flex items-center gap-3">
          <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-primary text-white">🚀</span>
          <div>
            <p class="text-sm font-semibold text-foreground">{{ __('public.home.public_focus') }}</p>
            <p class="text-xs text-muted-foreground">{{ __('public.home.public_focus_pages') }}</p>
          </div>
        </div>
        <p class="mt-4 text-sm leading-relaxed text-muted-foreground">{{ __('public.home.public_focus_desc') }}</p>
      </div>
    </div>
  </section>
</div>
@endsection
