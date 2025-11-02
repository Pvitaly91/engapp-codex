@extends('layouts.engram')

@section('title', 'Engram — платформа англійської практики')

@section('content')
<div class="space-y-20">
  <!-- Hero Section with Enhanced Visuals -->
  <section class="relative overflow-hidden rounded-3xl border border-border/80 bg-gradient-to-br from-primary/10 via-background to-secondary/10 shadow-soft">
    <!-- Animated Background Blobs -->
    <div class="absolute -left-24 top-10 h-56 w-56 animate-pulse rounded-full bg-primary/20 blur-3xl"></div>
    <div class="absolute -right-24 bottom-0 h-64 w-64 animate-pulse rounded-full bg-secondary/20 blur-3xl animation-delay-1000"></div>
    <div class="absolute left-1/2 top-1/2 h-48 w-48 -translate-x-1/2 -translate-y-1/2 rounded-full bg-accent/10 blur-3xl animation-delay-2000"></div>

    <div class="relative grid gap-10 px-6 py-12 md:grid-cols-2 md:px-12 md:py-16">
      <div class="space-y-6">
        <span class="inline-flex items-center gap-2 rounded-full bg-background/80 px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-primary shadow-sm ring-1 ring-primary/30 backdrop-blur-sm transition hover:scale-105">
          <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
          </svg>
          beta доступ
        </span>
        <div class="space-y-4">
          <h1 class="bg-gradient-to-r from-foreground via-primary to-foreground bg-clip-text text-3xl font-bold tracking-tight text-transparent md:text-5xl">
            Усе, що потрібно для граматики та практики англійської — в одному місці
          </h1>
          <p class="text-base leading-relaxed text-muted-foreground md:text-lg">
            Engram об'єднує конструктор граматичних тестів, бібліотеку готових добірок, теоретичні статті та інструменти з підказками ШІ. Створено для викладачів та команд, які швидко готують якісний контент.
          </p>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
          <a href="{{ route('catalog-tests.cards') }}" class="group inline-flex items-center justify-center gap-2 rounded-2xl bg-primary px-6 py-3.5 text-sm font-semibold text-primary-foreground shadow-lg transition hover:scale-105 hover:shadow-xl">
            Переглянути тести
            <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
          </a>
          <a href="{{ route('grammar-test') }}" class="group inline-flex items-center justify-center gap-2 rounded-2xl border-2 border-border bg-background/80 px-6 py-3.5 text-sm font-semibold text-foreground backdrop-blur-sm transition hover:border-primary hover:text-primary">
            Зібрати власний тест
            <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
          </a>
        </div>
        <dl class="grid gap-4 sm:grid-cols-2">
          <div class="group rounded-2xl border border-border/70 bg-background/70 p-5 backdrop-blur-sm transition hover:-translate-y-1 hover:border-primary/50 hover:shadow-md">
            <dt class="flex items-center gap-2 text-sm font-semibold text-foreground">
              <svg class="h-5 w-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
              </svg>
              Автоматизовані добірки
            </dt>
            <dd class="mt-2 text-sm leading-relaxed text-muted-foreground">Готові сценарії уроків із зазначеними рівнями, темами та підказками до кожного питання.</dd>
          </div>
          <div class="group rounded-2xl border border-border/70 bg-background/70 p-5 backdrop-blur-sm transition hover:-translate-y-1 hover:border-primary/50 hover:shadow-md">
            <dt class="flex items-center gap-2 text-sm font-semibold text-foreground">
              <svg class="h-5 w-5 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
              </svg>
              AI-підтримка
            </dt>
            <dd class="mt-2 text-sm leading-relaxed text-muted-foreground">Швидке пояснення відповідей, визначення рівня складності та генерація підказок за один клік.</dd>
          </div>
        </dl>
      </div>

      <!-- How it Works Card -->
      <div class="flex items-center justify-center">
        <div class="w-full max-w-sm space-y-5 rounded-3xl border border-border/70 bg-background/90 p-7 shadow-xl backdrop-blur-md transition hover:shadow-2xl">
          <div class="space-y-2">
            <p class="flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
              </svg>
              Шлях користувача
            </p>
            <h2 class="text-xl font-semibold text-foreground">Як працює Engram</h2>
          </div>
          <ol class="space-y-4 text-sm text-muted-foreground">
            <li class="flex gap-3">
              <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-primary to-primary/70 font-semibold text-primary-foreground shadow-sm">1</span>
              <span class="leading-relaxed">Обирайте тест з каталогу або згенеруйте власний під конкретний запит.</span>
            </li>
            <li class="flex gap-3">
              <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-secondary to-secondary/70 font-semibold text-secondary-foreground shadow-sm">2</span>
              <span class="leading-relaxed">Проходьте вправи, використовуючи підказки, пояснення та інструменти рецензування запитань.</span>
            </li>
            <li class="flex gap-3">
              <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-accent to-accent/70 font-semibold text-accent-foreground shadow-sm">3</span>
              <span class="leading-relaxed">Зберігайте результати, позначайте рівні, теги та повертайтесь до них у будь-який момент.</span>
            </li>
          </ol>
          <div class="rounded-2xl border border-dashed border-primary/50 bg-primary/5 p-4 text-sm text-muted-foreground backdrop-blur-sm">
            <p class="flex items-center gap-2 font-semibold text-primary">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
              </svg>
              Порада
            </p>
            <p class="mt-1.5 leading-relaxed">Скористайтеся пошуком, щоб миттєво знаходити сторінки з теорією та окремі тести за тегами.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Statistics Section -->
  @php
    $statLabels = [
        'tests' => ['label' => 'Готових тестів', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
        'questions' => ['label' => 'Питань у базі', 'icon' => 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        'pages' => ['label' => 'Сторінок теорії', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
        'tags' => ['label' => 'Тегів для фільтрації', 'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z'],
    ];
  @endphp
  <section aria-labelledby="stats-heading" class="space-y-8">
    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
      <div class="space-y-1">
        <h2 id="stats-heading" class="text-3xl font-bold text-foreground">Повна картина вашої бази</h2>
        <p class="text-sm text-muted-foreground">Статистика оновлюється автоматично, щойно ви додаєте нові питання або статті.</p>
      </div>
      <a href="{{ route('saved-tests.list') }}" class="group inline-flex items-center gap-2 text-sm font-semibold text-primary transition hover:text-primary/80">
        Переглянути всі тести
        <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
        </svg>
      </a>
    </div>
    <dl class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
      @foreach($statLabels as $key => $meta)
        <div class="group relative overflow-hidden rounded-2xl border border-border/70 bg-card p-6 shadow-soft transition hover:-translate-y-2 hover:shadow-xl">
          <div class="absolute right-0 top-0 h-24 w-24 translate-x-8 -translate-y-8 rounded-full bg-primary/5 transition group-hover:scale-150"></div>
          <dt class="relative flex items-center gap-3 text-sm font-medium text-muted-foreground">
            <svg class="h-5 w-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $meta['icon'] }}"/>
            </svg>
            {{ $meta['label'] }}
          </dt>
          <dd class="relative mt-3 text-3xl font-bold text-foreground">{{ number_format($stats[$key] ?? 0, 0, ',', ' ') }}</dd>
        </div>
      @endforeach
    </dl>
  </section>

  <!-- Features Section -->
  <section aria-labelledby="features-heading" class="space-y-8">
    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
      <div class="space-y-1">
        <h2 id="features-heading" class="text-3xl font-bold text-foreground">Що всередині Engram</h2>
        <p class="text-sm text-muted-foreground">Набір інструментів для створення, проходження та аналізу англомовних матеріалів.</p>
      </div>
    </div>
    @php
      $featureCards = [
        [
          'title' => 'Каталог граматичних тестів',
          'description' => 'Добірки запитань за рівнями CEFR, часами та спеціальними темами. Фільтруйте за тегами й отримуйте готовий набір для уроку.',
          'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
          'link' => route('catalog-tests.cards'),
          'cta' => 'Відкрити каталог',
          'color' => 'primary'
        ],
        [
          'title' => 'Конструктор тестів',
          'description' => 'Збирайте власний тест: обирайте питання, додавайте AI-вправи, налаштовуйте рівень складності та кількість завдань.',
          'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
          'link' => route('grammar-test'),
          'cta' => 'Створити тест',
          'color' => 'secondary'
        ],
        [
          'title' => 'Теоретичні сторінки',
          'description' => 'Стислі пояснення граматики з прикладами, таблицями та підказками українською. Зручно використовувати під час заняття.',
          'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
          'link' => route('pages.index'),
          'cta' => 'Перейти до теорії',
          'color' => 'accent'
        ],
        [
          'title' => 'Підтримка запитань та рецензій',
          'description' => 'Перевіряйте варіанти відповідей, отримуйте пояснення від ШІ та зберігайте рецензії для подальшого аналізу.',
          'icon' => 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z',
          'link' => route('question-review.index'),
          'cta' => 'Відкрити рецензії',
          'color' => 'success'
        ],
      ];
    @endphp
    <div class="grid gap-6 md:grid-cols-2">
      @foreach($featureCards as $feature)
        <article class="group relative flex h-full flex-col justify-between overflow-hidden rounded-3xl border border-border/80 bg-card p-7 shadow-soft transition hover:-translate-y-2 hover:border-{{ $feature['color'] }}/70 hover:shadow-xl">
          <div class="absolute -right-8 -top-8 h-32 w-32 rounded-full bg-{{ $feature['color'] }}/5 transition group-hover:scale-150"></div>
          <div class="relative space-y-4">
            <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-{{ $feature['color'] }}/10 ring-1 ring-{{ $feature['color'] }}/20 transition group-hover:scale-110 group-hover:bg-{{ $feature['color'] }} group-hover:ring-{{ $feature['color'] }}">
              <svg class="h-6 w-6 text-{{ $feature['color'] }} transition group-hover:text-{{ $feature['color'] }}-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $feature['icon'] }}"/>
              </svg>
            </span>
            <h3 class="text-lg font-semibold text-foreground">{{ $feature['title'] }}</h3>
            <p class="text-sm leading-relaxed text-muted-foreground">{{ $feature['description'] }}</p>
          </div>
          <a href="{{ $feature['link'] }}" class="relative mt-6 inline-flex items-center gap-2 text-sm font-semibold text-{{ $feature['color'] }} transition group-hover:gap-3">
            {{ $feature['cta'] }}
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
          </a>
        </article>
      @endforeach
    </div>
  </section>

  <!-- Latest Tests Section -->
  @if($latestTests->isNotEmpty())
    <section aria-labelledby="latest-tests-heading" class="space-y-8">
      <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div class="space-y-1">
          <h2 id="latest-tests-heading" class="text-3xl font-bold text-foreground">Останні збережені тести</h2>
          <p class="text-sm text-muted-foreground">Нове поповнення бази — швидкий спосіб знайти свіже натхнення для уроку.</p>
        </div>
        <a href="{{ route('catalog-tests.cards') }}" class="group inline-flex items-center gap-2 text-sm font-semibold text-primary transition hover:text-primary/80">
          Переглянути весь каталог
          <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
          </svg>
        </a>
      </div>
      <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($latestTests as $test)
          <article class="group flex h-full flex-col rounded-3xl border border-border/80 bg-card p-6 shadow-soft transition hover:-translate-y-2 hover:shadow-xl">
            <div class="space-y-3">
              <div class="flex items-center gap-2 text-xs text-muted-foreground">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Оновлено {{ optional($test->updated_at)->format('d.m.Y') }}
              </div>
              <h3 class="text-lg font-semibold text-foreground">{{ $test->name }}</h3>
              @if($test->description)
                <p class="text-sm leading-relaxed text-muted-foreground">{{ \Illuminate\Support\Str::limit(strip_tags($test->description), 120) }}</p>
              @endif
            </div>
            <dl class="mt-4 space-y-2 text-sm text-muted-foreground">
              <div class="flex items-center justify-between rounded-lg bg-muted/50 px-3 py-2">
                <dt class="flex items-center gap-2">
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                  </svg>
                  Завдань
                </dt>
                <dd class="font-semibold text-foreground">{{ $test->question_links_count }}</dd>
              </div>
              @php
                $levels = collect($test->filters['levels'] ?? $test->filters['level'] ?? [])->filter()->unique()->values();
              @endphp
              @if($levels->isNotEmpty())
                <div class="flex items-center justify-between rounded-lg bg-muted/50 px-3 py-2">
                  <dt class="flex items-center gap-2">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Рівні
                  </dt>
                  <dd class="font-semibold text-foreground">{{ $levels->implode(', ') }}</dd>
                </div>
              @endif
            </dl>
            <a href="{{ route('saved-test.js', $test->slug) }}" class="mt-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-secondary px-4 py-2.5 text-sm font-semibold text-secondary-foreground shadow-sm transition hover:scale-105 hover:shadow-md">
              Перейти до тесту
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
              </svg>
            </a>
          </article>
        @endforeach
      </div>
    </section>
  @endif

  <!-- Knowledge Base Section -->
  @if($featuredCategories->isNotEmpty() || $recentPages->isNotEmpty())
    <section aria-labelledby="knowledge-heading" class="space-y-8">
      <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div class="space-y-1">
          <h2 id="knowledge-heading" class="text-3xl font-bold text-foreground">Теорія та матеріали</h2>
          <p class="text-sm text-muted-foreground">Підбірки пояснень, які легко переглядати під час уроку.</p>
        </div>
        <a href="{{ route('pages.index') }}" class="group inline-flex items-center gap-2 text-sm font-semibold text-primary transition hover:text-primary/80">
          Відкрити всі сторінки
          <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
          </svg>
        </a>
      </div>
      @if($featuredCategories->isNotEmpty())
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
          @foreach($featuredCategories as $category)
            <article class="group rounded-3xl border border-border/80 bg-card p-6 shadow-soft transition hover:-translate-y-2 hover:border-accent/70 hover:shadow-xl">
              <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-2xl bg-accent/10 ring-1 ring-accent/20 transition group-hover:scale-110 group-hover:bg-accent">
                <svg class="h-5 w-5 text-accent transition group-hover:text-accent-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                </svg>
              </div>
              <h3 class="text-base font-semibold text-foreground">{{ $category->title }}</h3>
              <p class="mt-2 text-sm text-muted-foreground">{{ $category->pages_count }} матеріал(и/ів)</p>
              <a href="{{ route('pages.category', $category->slug) }}" class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-accent transition group-hover:gap-3">
                Переглянути
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
              </a>
            </article>
          @endforeach
        </div>
      @endif
      @if($recentPages->isNotEmpty())
        <div class="grid gap-6 md:grid-cols-2">
          @foreach($recentPages as $page)
            <article class="group flex h-full flex-col justify-between rounded-3xl border border-border/80 bg-card p-6 shadow-soft transition hover:-translate-y-2 hover:shadow-xl">
              <div class="space-y-3">
                <span class="inline-flex items-center gap-2 rounded-full bg-muted px-3 py-1 text-xs font-semibold text-muted-foreground">
                  <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                  </svg>
                  {{ $page->category->title }}
                </span>
                <h3 class="text-lg font-semibold text-foreground">{{ $page->title }}</h3>
                <p class="text-sm leading-relaxed text-muted-foreground">{{ \Illuminate\Support\Str::limit(strip_tags($page->text), 140) }}</p>
              </div>
              <a href="{{ route('pages.show', [$page->category->slug, $page->slug]) }}" class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-primary transition group-hover:gap-3">
                Читати далі
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
              </a>
            </article>
          @endforeach
        </div>
      @endif
    </section>
  @endif

  <!-- CTA Section -->
  <section class="relative overflow-hidden rounded-3xl border border-border/70 bg-gradient-to-br from-secondary/10 via-background to-primary/10 p-8 text-center shadow-xl md:p-12">
    <div class="absolute -left-16 bottom-0 h-48 w-48 rounded-full bg-secondary/20 blur-3xl"></div>
    <div class="absolute -right-16 top-0 h-48 w-48 rounded-full bg-primary/20 blur-3xl"></div>
    <div class="relative mx-auto max-w-2xl space-y-6">
      <h2 class="text-3xl font-bold text-foreground md:text-4xl">Готові спробувати Engram у роботі?</h2>
      <p class="text-base leading-relaxed text-muted-foreground">Напишіть нам, щоб отримати доступ, презентувати платформу команді або поставити запитання.</p>
      <div class="flex flex-col items-center justify-center gap-4 sm:flex-row">
        <a href="mailto:hello@engram.app" class="group inline-flex items-center justify-center gap-2 rounded-2xl bg-primary px-8 py-3.5 text-sm font-semibold text-primary-foreground shadow-lg transition hover:scale-105 hover:shadow-xl">
          Запросити демо
          <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
          </svg>
        </a>
        <a href="https://t.me/engram_app" class="group inline-flex items-center gap-2 text-sm font-semibold text-primary transition hover:text-primary/80">
          Написати в Telegram
          <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
          </svg>
        </a>
      </div>
    </div>
  </section>
</div>

<style>
@keyframes pulse {
  0%, 100% { opacity: 0.2; }
  50% { opacity: 0.4; }
}
.animation-delay-1000 {
  animation-delay: 1s;
}
.animation-delay-2000 {
  animation-delay: 2s;
}
</style>
@endsection
