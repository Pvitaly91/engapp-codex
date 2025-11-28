@extends('layouts.engram')

@section('title', 'Gramlyze — Вивчай англійську ефективно')

@section('content')
<div class="space-y-24">
  <!-- HERO: Immersive Full-Width -->
  <section id="hero" data-animate class="relative -mx-4 -mt-10 overflow-hidden bg-gradient-to-b from-[hsl(var(--primary)/0.08)] via-background to-background px-4 pb-16 pt-20 md:pb-24 md:pt-28">
    <div class="absolute inset-0 overflow-hidden">
      <div class="absolute -left-20 top-0 h-[500px] w-[500px] rounded-full bg-gradient-to-br from-primary/30 to-transparent blur-[120px]"></div>
      <div class="absolute -right-20 top-40 h-[400px] w-[400px] rounded-full bg-gradient-to-br from-secondary/25 to-transparent blur-[100px]"></div>
      <div class="absolute bottom-0 left-1/2 h-[300px] w-[600px] -translate-x-1/2 rounded-full bg-gradient-to-t from-accent/15 to-transparent blur-[80px]"></div>
    </div>
    
    <div class="page-shell relative mx-auto">
      <div class="mx-auto max-w-4xl text-center" data-animate data-animate-delay="100">
        <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-4 py-1.5 backdrop-blur-sm">
          <span class="relative flex h-2 w-2">
            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-primary opacity-75"></span>
            <span class="relative inline-flex h-2 w-2 rounded-full bg-primary"></span>
          </span>
          <span class="text-xs font-medium text-primary">Платформа для вивчення англійської</span>
        </div>
        
        <h1 class="mb-6 text-4xl font-extrabold leading-tight tracking-tight md:text-6xl lg:text-7xl">
          <span class="block text-foreground">Опануй англійську</span>
          <span class="bg-gradient-to-r from-primary via-secondary to-accent bg-clip-text text-transparent">на новому рівні</span>
        </h1>
        
        <p class="mx-auto mb-10 max-w-2xl text-lg leading-relaxed text-muted-foreground md:text-xl">
          Інтерактивні тести, AI-підказки та структурована теорія — все для ефективного вивчення граматики та лексики англійської мови
        </p>
        
        <div class="flex flex-col items-center justify-center gap-4 sm:flex-row">
          <a href="{{ route('catalog.tests-cards') }}" class="group relative inline-flex items-center justify-center gap-2 overflow-hidden rounded-full bg-gradient-to-r from-primary to-secondary px-8 py-4 text-base font-bold text-white shadow-xl transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl hover:shadow-primary/25">
            <span>Почати навчання</span>
            <svg class="h-5 w-5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </a>
          <a href="{{ route('pages.index') }}" class="group inline-flex items-center gap-2 rounded-full border-2 border-foreground/20 bg-background/50 px-8 py-4 text-base font-semibold text-foreground backdrop-blur-sm transition-all duration-300 hover:border-primary hover:bg-primary/5 hover:text-primary">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13" />
            </svg>
            <span>Переглянути теорію</span>
          </a>
        </div>
      </div>
      
      <!-- Floating Stats Cards -->
      <div class="mt-16 grid gap-4 sm:grid-cols-3 md:mt-20" data-animate data-animate-delay="300">
        @php
          $heroStats = [
            ['icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'value' => $stats['tests'] ?? '500+', 'label' => 'Готових тестів', 'color' => 'primary'],
            ['icon' => 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'value' => $stats['questions'] ?? '10 000+', 'label' => 'Питань у базі', 'color' => 'secondary'],
            ['icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z', 'value' => $stats['tags'] ?? '200+', 'label' => 'Тегів і категорій', 'color' => 'accent'],
          ];
        @endphp
        @foreach ($heroStats as $stat)
          <div class="group relative overflow-hidden rounded-3xl border border-border/50 bg-card/80 p-6 shadow-lg backdrop-blur-sm transition-all duration-300 hover:-translate-y-2 hover:shadow-xl">
            <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-{{ $stat['color'] }}/10 transition-transform duration-500 group-hover:scale-150"></div>
            <div class="relative">
              <div class="mb-3 inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-{{ $stat['color'] }}/15 text-{{ $stat['color'] }}">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $stat['icon'] }}" />
                </svg>
              </div>
              <p class="text-3xl font-bold text-foreground md:text-4xl">{{ $stat['value'] }}</p>
              <p class="mt-1 text-sm text-muted-foreground">{{ $stat['label'] }}</p>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  <!-- FEATURES: Bento Grid -->
  <section id="features" class="space-y-12" data-animate>
    <div class="text-center" data-animate data-animate-delay="80">
      <span class="mb-4 inline-block rounded-full bg-secondary/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-secondary">Можливості</span>
      <h2 class="mb-4 text-3xl font-bold tracking-tight md:text-5xl">Все для ефективного навчання</h2>
      <p class="mx-auto max-w-2xl text-muted-foreground">Платформа, що поєднує сучасні методики викладання з інноваційними технологіями</p>
    </div>
    
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3" data-animate data-animate-delay="160">
      <!-- Large Feature Card -->
      <div class="group relative col-span-full overflow-hidden rounded-[2rem] border border-border/50 bg-gradient-to-br from-primary/5 via-card to-secondary/5 p-8 shadow-lg transition-all duration-500 hover:shadow-xl lg:col-span-2">
        <div class="absolute right-0 top-0 h-80 w-80 translate-x-20 translate-y-[-50%] rounded-full bg-gradient-to-br from-primary/20 to-secondary/20 blur-3xl transition-transform duration-700 group-hover:translate-x-10"></div>
        <div class="relative grid gap-8 md:grid-cols-2">
          <div class="space-y-6">
            <div class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-primary to-secondary text-white shadow-lg">
              <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
              </svg>
            </div>
            <div>
              <h3 class="mb-2 text-2xl font-bold">Каталог тестів</h3>
              <p class="leading-relaxed text-muted-foreground">Понад 500 готових тестів, організованих за рівнями CEFR, граматичними темами та типами вправ. Фільтруйте за тегами та знаходьте потрібний контент за секунди.</p>
            </div>
            <a href="{{ route('catalog.tests-cards') }}" class="inline-flex items-center gap-2 font-semibold text-primary transition-all hover:gap-3">
              Відкрити каталог
              <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
              </svg>
            </a>
          </div>
          <div class="relative hidden items-center justify-center md:flex">
            <div class="grid grid-cols-2 gap-3">
              @php
                $levels = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];
              @endphp
              @foreach ($levels as $i => $level)
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl border border-border/50 bg-background/80 text-lg font-bold shadow-sm transition-all duration-300 hover:scale-110 hover:border-primary/50 hover:shadow-md" style="animation-delay: {{ $i * 100 }}ms">
                  {{ $level }}
                </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>
      
      <!-- AI Feature Card -->
      <div class="group relative overflow-hidden rounded-[2rem] border border-border/50 bg-gradient-to-br from-accent/5 via-card to-accent/10 p-8 shadow-lg transition-all duration-500 hover:shadow-xl">
        <div class="absolute -bottom-10 -right-10 h-40 w-40 rounded-full bg-accent/20 blur-3xl transition-transform duration-500 group-hover:scale-150"></div>
        <div class="relative space-y-6">
          <div class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-accent to-warning text-white shadow-lg">
            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
          </div>
          <div>
            <h3 class="mb-2 text-xl font-bold">AI-асистент</h3>
            <p class="text-sm leading-relaxed text-muted-foreground">Отримуйте миттєві пояснення помилок, підказки та детальний розбір відповідей від штучного інтелекту</p>
          </div>
          <a href="{{ route('question-review.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-accent transition-all hover:gap-3">
            Спробувати
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
            </svg>
          </a>
        </div>
      </div>
      
      <!-- Theory Card -->
      <div class="group relative overflow-hidden rounded-[2rem] border border-border/50 bg-card p-8 shadow-lg transition-all duration-500 hover:shadow-xl">
        <div class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-info/15 blur-2xl transition-transform duration-500 group-hover:scale-150"></div>
        <div class="relative space-y-6">
          <div class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-info/15 text-info">
            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13" />
            </svg>
          </div>
          <div>
            <h3 class="mb-2 text-xl font-bold">База знань</h3>
            <p class="text-sm leading-relaxed text-muted-foreground">Структурована теорія граматики та лексики з прикладами, таблицями та візуальними схемами</p>
          </div>
          <a href="{{ route('pages.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-info transition-all hover:gap-3">
            Читати теорію
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
            </svg>
          </a>
        </div>
      </div>
      
      <!-- Constructor Card -->
      <div class="group relative overflow-hidden rounded-[2rem] border border-border/50 bg-card p-8 shadow-lg transition-all duration-500 hover:shadow-xl">
        <div class="absolute -bottom-10 -left-10 h-32 w-32 rounded-full bg-success/15 blur-2xl transition-transform duration-500 group-hover:scale-150"></div>
        <div class="relative space-y-6">
          <div class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-success/15 text-success">
            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z" />
            </svg>
          </div>
          <div>
            <h3 class="mb-2 text-xl font-bold">Конструктор</h3>
            <p class="text-sm leading-relaxed text-muted-foreground">Створюйте власні тести з різними типами питань: вибір, введення, drag-and-drop та matching</p>
          </div>
          <a href="{{ route('grammar-test') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-success transition-all hover:gap-3">
            Створити тест
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
            </svg>
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- HOW IT WORKS: Steps -->
  <section id="how-it-works" class="space-y-12" data-animate>
    <div class="text-center" data-animate data-animate-delay="80">
      <span class="mb-4 inline-block rounded-full bg-primary/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-primary">Як це працює</span>
      <h2 class="mb-4 text-3xl font-bold tracking-tight md:text-5xl">Три кроки до результату</h2>
      <p class="mx-auto max-w-2xl text-muted-foreground">Простий та ефективний процес навчання</p>
    </div>
    
    <div class="relative" data-animate data-animate-delay="160">
      <!-- Connection Line (Desktop) -->
      <div class="absolute left-0 right-0 top-1/2 hidden h-1 -translate-y-1/2 bg-gradient-to-r from-transparent via-border to-transparent md:block"></div>
      
      <div class="grid gap-8 md:grid-cols-3">
        @php
          $steps = [
            [
              'step' => '01',
              'title' => 'Обери тему',
              'description' => 'Використай пошук або фільтри каталогу, щоб знайти потрібну граматичну тему чи рівень складності',
              'icon' => 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z',
              'color' => 'primary',
            ],
            [
              'step' => '02',
              'title' => 'Проходь тести',
              'description' => 'Виконуй інтерактивні вправи різних форматів та отримуй миттєвий зворотний зв\'язок',
              'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
              'color' => 'secondary',
            ],
            [
              'step' => '03',
              'title' => 'Аналізуй результати',
              'description' => 'Отримай детальні пояснення від AI та вивчай теорію для закріплення знань',
              'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
              'color' => 'accent',
            ],
          ];
        @endphp
        @foreach ($steps as $step)
          <div class="group relative">
            <div class="relative overflow-hidden rounded-3xl border border-border/50 bg-card p-8 shadow-lg transition-all duration-300 hover:-translate-y-2 hover:shadow-xl">
              <div class="absolute -right-6 -top-6 text-8xl font-black text-{{ $step['color'] }}/5 transition-all duration-500 group-hover:text-{{ $step['color'] }}/10">{{ $step['step'] }}</div>
              <div class="relative">
                <div class="relative z-10 mb-6 inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-{{ $step['color'] }}/10 text-{{ $step['color'] }} ring-4 ring-background shadow-lg">
                  <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $step['icon'] }}" />
                  </svg>
                </div>
                <h3 class="mb-3 text-xl font-bold">{{ $step['title'] }}</h3>
                <p class="leading-relaxed text-muted-foreground">{{ $step['description'] }}</p>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  <!-- TESTIMONIAL / HIGHLIGHT -->
  <section id="highlight" class="relative overflow-hidden rounded-[2.5rem] border border-border/50 bg-gradient-to-br from-primary/10 via-card to-secondary/10 p-10 shadow-xl md:p-16" data-animate>
    <div class="absolute -left-40 -top-40 h-80 w-80 rounded-full bg-primary/20 blur-[100px]"></div>
    <div class="absolute -bottom-40 -right-40 h-80 w-80 rounded-full bg-secondary/20 blur-[100px]"></div>
    
    <div class="relative mx-auto max-w-4xl text-center">
      <div class="mb-8 inline-flex items-center gap-3 rounded-full border border-primary/20 bg-primary/5 px-5 py-2 backdrop-blur-sm">
        <svg class="h-5 w-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
        </svg>
        <span class="text-sm font-medium text-primary">Powered by AI</span>
      </div>
      
      <h2 class="mb-6 text-3xl font-bold leading-tight md:text-5xl">
        Навчайся розумніше, <br class="hidden sm:block" />
        <span class="bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">а не більше</span>
      </h2>
      
      <p class="mx-auto mb-10 max-w-2xl text-lg text-muted-foreground">
        Наша AI-система аналізує твої відповіді, виявляє слабкі місця та надає персоналізовані рекомендації для покращення
      </p>
      
      <div class="flex flex-col items-center justify-center gap-4 sm:flex-row">
        <a href="{{ route('catalog.tests-cards') }}" class="group inline-flex items-center gap-2 rounded-full bg-foreground px-8 py-4 text-base font-bold text-background shadow-xl transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
          <span>Розпочати безкоштовно</span>
          <svg class="h-5 w-5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
          </svg>
        </a>
        <a href="{{ route('question-review.index') }}" class="inline-flex items-center gap-2 text-base font-semibold text-foreground transition-all hover:text-primary">
          Переглянути AI-рецензії
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
          </svg>
        </a>
      </div>
    </div>
  </section>

  <!-- QUICK LINKS / CTA Grid -->
  <section id="quick-links" class="space-y-8" data-animate>
    <div class="text-center">
      <h2 class="mb-4 text-2xl font-bold md:text-3xl">Швидкий доступ</h2>
      <p class="text-muted-foreground">Оберіть розділ для початку роботи</p>
    </div>
    
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4" data-animate data-animate-delay="100">
      @php
        $quickLinks = [
          ['title' => 'Каталог тестів', 'description' => 'Усі доступні тести', 'route' => 'catalog.tests-cards', 'icon' => '📚', 'color' => 'from-violet-500 to-purple-600'],
          ['title' => 'Теорія', 'description' => 'Граматика та лексика', 'route' => 'pages.index', 'icon' => '📖', 'color' => 'from-blue-500 to-cyan-600'],
          ['title' => 'AI-рецензії', 'description' => 'Розбір відповідей', 'route' => 'question-review.index', 'icon' => '🤖', 'color' => 'from-orange-500 to-amber-600'],
          ['title' => 'Конструктор', 'description' => 'Створити тест', 'route' => 'grammar-test', 'icon' => '🔧', 'color' => 'from-emerald-500 to-teal-600'],
        ];
      @endphp
      @foreach ($quickLinks as $link)
        <a href="{{ route($link['route']) }}" class="group relative overflow-hidden rounded-2xl border border-border/50 bg-card p-6 shadow-md transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
          <div class="absolute inset-0 bg-gradient-to-br {{ $link['color'] }} opacity-0 transition-opacity duration-300 group-hover:opacity-5"></div>
          <div class="relative">
            <span class="mb-4 block text-4xl">{{ $link['icon'] }}</span>
            <h3 class="mb-1 text-lg font-bold transition-colors group-hover:text-primary">{{ $link['title'] }}</h3>
            <p class="text-sm text-muted-foreground">{{ $link['description'] }}</p>
          </div>
          <svg class="absolute bottom-6 right-6 h-5 w-5 text-muted-foreground/30 transition-all duration-300 group-hover:text-primary group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
          </svg>
        </a>
      @endforeach
    </div>
  </section>
</div>

<style>
  @keyframes shimmer {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
  }
</style>
@endsection
