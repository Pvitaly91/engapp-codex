@extends('layouts.engram')

@section('title', 'Gramlyze — платформа англійської практики')

@section('content')
<div class="space-y-24">
  <!-- HERO -->
  <section id="hero" data-animate class="relative overflow-hidden rounded-[2.5rem] border border-border/80 bg-gradient-to-br from-primary/10 via-background to-secondary/15 p-10 shadow-soft md:p-16">
    <div class="absolute -top-32 right-10 h-64 w-64 rounded-full bg-primary/20 blur-3xl"></div>
    <div class="absolute -bottom-32 left-4 h-72 w-72 rounded-full bg-accent/15 blur-3xl"></div>
    <div class="relative grid gap-12 md:grid-cols-[1.35fr_1fr]">
      <div class="space-y-8" data-animate data-animate-delay="100">
        <span class="inline-flex items-center gap-2 rounded-full bg-background/70 px-5 py-1.5 text-xs font-semibold uppercase tracking-[0.4em] text-primary backdrop-blur">
          🚀 beta доступ
        </span>
        <div class="space-y-6">
          <h1 class="text-4xl font-bold tracking-tight text-foreground md:text-6xl">
            Революція у вивченні <span class="bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">англійської мови</span>
          </h1>
          <p class="text-base leading-relaxed text-muted-foreground md:text-xl">
            Gramlyze — комплексна платформа для викладачів англійської мови. Конструктор тестів, база теорії, AI-помічники та аналітика в одному місці. Підготовка до уроку займає хвилини, а не години.
          </p>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
          <a href="{{ route('catalog-tests.cards') }}" class="group inline-flex items-center justify-center gap-2 rounded-2xl bg-primary px-7 py-3.5 text-sm font-semibold text-primary-foreground shadow-lg transition hover:-translate-y-0.5 hover:shadow-xl">
            📚 Перейти до каталогу
            <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </a>
          <a href="{{ route('grammar-test') }}" class="group inline-flex items-center justify-center gap-2 rounded-2xl border border-border bg-background/80 px-7 py-3.5 text-sm font-semibold text-foreground backdrop-blur transition hover:border-primary hover:text-primary">
            ✨ Зібрати власний тест
            <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </a>
        </div>
        @php
          $heroHighlights = [
            ['label' => '🎯 Каталоги за CEFR та темами', 'icon' => 'M19 11H5M7 7h10M9 3h6a2 2 0 012 2v2H7V5a2 2 0 012-2zM5 11v6a2 2 0 002 2h10a2 2 0 002-2v-6'],
            ['label' => '🤖 AI-перевірка відповідей', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['label' => '👥 Спільна робота команди', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2a3 3 0 00-.132-.894M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2a3 3 0 01.132-.894M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a3 3 0 11-6 0 3 3 0 016 0zM9 10a3 3 0 11-6 0 3 3 0 016 0z'],
          ];
        @endphp
        <dl class="grid gap-4 sm:grid-cols-3">
          @foreach ($heroHighlights as $item)
            <div class="group rounded-2xl border border-border/70 bg-background/80 p-4 backdrop-blur transition hover:-translate-y-1 hover:border-primary/50 hover:shadow-lg" data-animate data-animate-delay="{{ 200 + $loop->index * 80 }}">
              <dt class="flex items-center gap-3 text-sm font-semibold text-foreground">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-primary/10 text-primary transition group-hover:scale-110">
                  <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}" />
                  </svg>
                </span>
                {{ $item['label'] }}
              </dt>
            </div>
          @endforeach
        </dl>
      </div>

      <div class="space-y-6 rounded-3xl border border-border/60 bg-card/90 p-6 shadow-xl backdrop-blur" data-animate data-animate-delay="250">
        <div class="space-y-3">
          <p class="text-xs font-semibold uppercase tracking-[0.3em] text-muted-foreground">💡 Чому Gramlyze?</p>
          <h2 class="text-2xl font-semibold text-foreground">Все необхідне в одному місці</h2>
        </div>
        <div class="space-y-4 text-sm text-muted-foreground">
          <p class="rounded-2xl border border-dashed border-primary/40 bg-primary/10 p-4 text-primary">
            🎓 Платформа об'єднує конструктор тестів, базу теорії та AI-аналіз в єдину екосистему для професійного викладання англійської.
          </p>
          <ul class="space-y-3">
            <li class="flex items-start gap-3">
              <span class="mt-1 h-2.5 w-2.5 rounded-full bg-primary"></span>
              <span>⚡ Зберігайте час: готові каталоги з тегами дозволяють зібрати урок за лічені хвилини.</span>
            </li>
            <li class="flex items-start gap-3">
              <span class="mt-1 h-2.5 w-2.5 rounded-full bg-secondary"></span>
              <span>🔍 AI-аналіз: автоматичні рецензії виявляють типові помилки та створюють персоналізовані пояснення.</span>
            </li>
            <li class="flex items-start gap-3">
              <span class="mt-1 h-2.5 w-2.5 rounded-full bg-accent"></span>
              <span>📖 Повна база знань: теорія, переклади та вправи доступні всій команді.</span>
            </li>
            <li class="flex items-start gap-3">
              <span class="mt-1 h-2.5 w-2.5 rounded-full bg-success"></span>
              <span>📊 Аналітика: відстежуйте прогрес студентів та визначайте сильні й слабкі місця.</span>
            </li>
          </ul>
        </div>
        <div class="grid gap-3 sm:grid-cols-2">
          <div class="rounded-2xl border border-border/60 bg-background/60 p-4 text-xs uppercase tracking-[0.3em] text-muted-foreground">
            <span class="text-[0.6rem] font-semibold text-primary">Основний логотип</span>
            <div class="mt-3 flex justify-start">
              <x-gramlyze-logo size="h-10 w-10" />
            </div>
          </div>
          <div class="rounded-2xl border border-dashed border-primary/40 bg-primary/5 p-4 text-xs uppercase tracking-[0.3em] text-primary">
            <span class="text-[0.6rem] font-semibold">Альтернативний бейдж</span>
            <div class="mt-3 flex justify-start">
              <x-gramlyze-logo variant="badge" />
            </div>
          </div>
        </div>
        <div class="grid gap-4 border-t border-border/60 pt-4 text-sm">
          <div class="flex items-center justify-between rounded-2xl border border-border/60 bg-background/70 px-4 py-3">
            <span class="font-semibold text-muted-foreground">Виділити тему</span>
            <span class="text-sm font-semibold text-primary">+ теги & рівні CEFR</span>
          </div>
          <div class="flex items-center justify-between rounded-2xl border border-border/60 bg-background/70 px-4 py-3">
            <span class="font-semibold text-muted-foreground">Поділитися тестом</span>
            <span class="text-sm font-semibold text-secondary">посиланням або PDF</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- PRODUCT MAP -->
  @php
    $productMap = [
      [
        'title' => '📚 Каталог тестів',
        'description' => 'Добірки завдань за рівнями CEFR (A1-C2), граматичними часами, професійними контекстами. Зручна фільтрація за тегами, темами та обсягом.',
        'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
        'cta' => '🔍 Відкрити каталог',
        'link' => route('catalog-tests.cards'),
        'accent' => 'primary'
      ],
      [
        'title' => '⚙️ Конструктор тестів',
        'description' => 'Створюйте власні вправи з нуля або використовуйте шаблони. Додавайте AI-підказки, налаштовуйте складність і контролюйте кількість кроків.',
        'icon' => 'M4 6h16M4 10h16M4 14h16M4 18h7',
        'cta' => '✨ Створити тест',
        'link' => route('grammar-test'),
        'accent' => 'secondary'
      ],
      [
        'title' => '📖 Теоретичні сторінки',
        'description' => 'Структуровані конспекти з граматики, лексики та фонетики. Таблиці, приклади та швидкі нагадування українською мовою.',
        'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13',
        'cta' => '📚 Перейти до теорії',
        'link' => route('pages.index'),
        'accent' => 'accent'
      ],
      [
        'title' => '🔬 Рецензії та аналіз',
        'description' => 'AI-пояснення правильних відповідей, порівняння варіантів, збереження персональних коментарів для кожного студента або групи.',
        'icon' => 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z',
        'cta' => '📊 Переглянути рецензії',
        'link' => route('question-review.index'),
        'accent' => 'success'
      ],
    ];
  @endphp
  <section id="solutions" class="space-y-8" data-animate>
    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between" data-animate data-animate-delay="100">
      <div class="space-y-2">
        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-primary">🗺️ Мапа платформи</p>
        <h2 class="text-3xl font-bold text-foreground md:text-4xl">Інструменти платформи Gramlyze</h2>
        <p class="max-w-2xl text-base leading-relaxed text-muted-foreground">Кожний модуль взаємопов'язаний: теги з теорії доступні у тестах, AI-рекомендації видно у каталозі, а результати зберігаються у спільному просторі команди.</p>
      </div>
    </div>
    <div class="md:hidden" data-animate data-animate-delay="160">
      <div class="relative" data-slider>
        <div class="flex snap-x snap-mandatory gap-4 overflow-x-auto pb-8 pl-2" data-slider-track>
          @foreach ($productMap as $card)
            <article class="group relative flex min-w-[85%] basis-[85%] flex-col justify-between overflow-hidden rounded-3xl border border-border/70 bg-card p-6 shadow-soft transition focus-within:ring-2 focus-within:ring-{{ $card['accent'] }}/50">
              <div class="absolute -right-6 -top-6 h-32 w-32 rounded-full bg-{{ $card['accent'] }}/10 transition group-hover:scale-150"></div>
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
                {{ $card['cta'] }}
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
              </a>
            </article>
          @endforeach
        </div>
        <div class="mt-2 flex items-center justify-between pr-2">
          <button type="button" data-slider-prev aria-label="Попередній слайд" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-border bg-background text-sm font-medium text-muted-foreground transition disabled:opacity-40">‹</button>
          <div class="flex items-center gap-2" data-slider-dots></div>
          <button type="button" data-slider-next aria-label="Наступний слайд" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-border bg-background text-sm font-medium text-muted-foreground transition disabled:opacity-40">›</button>
        </div>
      </div>
    </div>
    <div class="hidden gap-6 md:grid md:grid-cols-2" data-animate data-animate-delay="220">
      @foreach ($productMap as $card)
        <article class="group relative flex h-full flex-col justify-between overflow-hidden rounded-3xl border border-border/70 bg-card p-8 shadow-soft transition hover:-translate-y-2 hover:border-{{ $card['accent'] }}/60 hover:shadow-xl">
          <div class="absolute -right-6 -top-6 h-32 w-32 rounded-full bg-{{ $card['accent'] }}/10 transition group-hover:scale-150"></div>
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
            {{ $card['cta'] }}
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </a>
        </article>
      @endforeach
    </div>
  </section>

  <!-- STATS -->
  @php
    $statLabels = [
        'tests' => ['label' => '📝 Готових тестів', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'color' => 'primary'],
        'questions' => ['label' => '❓ Питань у базі', 'icon' => 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'secondary'],
        'pages' => ['label' => '📄 Сторінок теорії', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253', 'color' => 'accent'],
        'tags' => ['label' => '🏷️ Тегів для фільтрації', 'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z', 'color' => 'success'],
    ];
  @endphp
  <section id="metrics" class="space-y-8" data-animate>
    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between" data-animate data-animate-delay="100">
      <div class="space-y-2">
        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-primary">📈 Дані вашої бази</p>
        <h2 class="text-3xl font-bold text-foreground md:text-4xl">Статистика в реальному часі</h2>
        <p class="max-w-2xl text-base leading-relaxed text-muted-foreground">Коли ви додаєте нові завдання або редагуєте теорію, показники перераховуються автоматично й доступні всій команді миттєво.</p>
      </div>
      <a href="{{ route('saved-tests.list') }}" class="group inline-flex items-center gap-2 self-start rounded-2xl border border-border px-5 py-2.5 text-sm font-semibold text-muted-foreground transition hover:border-primary hover:text-primary hover:shadow-lg">
        🔗 Переглянути всі тести
        <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
        </svg>
      </a>
    </div>
    <div class="md:hidden" data-animate data-animate-delay="160">
      <div class="relative" data-slider>
        <div class="flex snap-x snap-mandatory gap-4 overflow-x-auto pb-8 pl-2" data-slider-track>
          @foreach ($statLabels as $key => $meta)
            <article class="group relative min-w-[80%] basis-[80%] overflow-hidden rounded-3xl border border-border/70 bg-card p-6 shadow-soft transition hover:-translate-y-1 hover:shadow-lg">
              <div class="absolute right-0 top-0 h-28 w-28 translate-x-10 -translate-y-10 rounded-full bg-{{ $meta['color'] }}/10 transition group-hover:scale-110"></div>
              <div class="relative flex items-center gap-3 text-sm font-medium text-muted-foreground">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-{{ $meta['color'] }}/10 text-{{ $meta['color'] }}">
                  <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $meta['icon'] }}" />
                  </svg>
                </span>
                {{ $meta['label'] }}
              </div>
              <p class="relative mt-5 text-4xl font-bold tracking-tight text-foreground">{{ number_format($stats[$key] ?? 0, 0, ',', ' ') }}</p>
            </article>
          @endforeach
        </div>
        <div class="mt-2 flex items-center justify-between pr-2">
          <button type="button" data-slider-prev aria-label="Попередній слайд" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-border bg-background text-sm font-medium text-muted-foreground transition disabled:opacity-40">‹</button>
          <div class="flex items-center gap-2" data-slider-dots></div>
          <button type="button" data-slider-next aria-label="Наступний слайд" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-border bg-background text-sm font-medium text-muted-foreground transition disabled:opacity-40">›</button>
        </div>
      </div>
    </div>
    <dl class="hidden gap-6 sm:grid sm:grid-cols-2 lg:grid-cols-4" data-animate data-animate-delay="220">
      @foreach ($statLabels as $key => $meta)
        <div class="group relative overflow-hidden rounded-3xl border border-border/70 bg-card p-6 shadow-soft transition hover:-translate-y-2 hover:border-{{ $meta['color'] }}/50 hover:shadow-xl">
          <div class="absolute right-0 top-0 h-28 w-28 translate-x-10 -translate-y-10 rounded-full bg-{{ $meta['color'] }}/10 transition group-hover:scale-150"></div>
          <dt class="relative flex items-center gap-3 text-sm font-medium text-muted-foreground">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-{{ $meta['color'] }}/10 text-{{ $meta['color'] }}">
              <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $meta['icon'] }}" />
              </svg>
            </span>
            {{ $meta['label'] }}
          </dt>
          <dd class="relative mt-5 text-4xl font-bold tracking-tight text-foreground">{{ number_format($stats[$key] ?? 0, 0, ',', ' ') }}</dd>
        </div>
      @endforeach
    </dl>
  </section>

  <!-- WORKFLOW -->
  @php
    $workflow = [
      ['step' => '1️⃣', 'title' => 'Знайдіть потрібний набір завдань', 'description' => 'Використовуйте фільтри каталогу за рівнем CEFR, граматичною темою або професійним доменом. Збережіть улюблені шаблони для швидкого повторного використання.'],
      ['step' => '2️⃣', 'title' => 'Налаштуйте тест під конкретну групу', 'description' => 'Додавайте власні питання, змінюйте порядок завдань, налаштовуйте кількість кроків, додавайте AI-підказки та персоналізовані пояснення.'],
      ['step' => '3️⃣', 'title' => 'Ведіть урок та збирайте результати', 'description' => 'Діліться посиланням із студентами або експортуйте тест в PDF. Результати відстежуються автоматично, а AI-аналіз виявляє типові помилки.'],
      ['step' => '4️⃣', 'title' => 'Аналізуйте та покращуйте', 'description' => 'Переглядайте детальні рецензії, теги помилок та нотатки команди. Формуйте наступні добірки на основі AI-рекомендацій та статистики успішності.'],
    ];
  @endphp
  <section id="workflow" class="space-y-8" data-animate>
    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between" data-animate data-animate-delay="100">
      <div class="space-y-2">
        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-primary">⚡ Процес роботи</p>
        <h2 class="text-3xl font-bold text-foreground md:text-4xl">Від пошуку до аналізу — за один потік</h2>
        <p class="max-w-2xl text-base leading-relaxed text-muted-foreground">Gramlyze структурує робочий день викладача: ви не губитеся між Google-доками і таблицями, а працюєте в єдиній системі з усіма необхідними інструментами.</p>
      </div>
    </div>
    <div class="md:hidden" data-animate data-animate-delay="160">
      <div class="relative" data-slider>
        <div class="flex snap-x snap-mandatory gap-4 overflow-x-auto pb-8 pl-2" data-slider-track>
          @foreach ($workflow as $item)
            <article class="group relative min-w-[85%] basis-[85%] overflow-hidden rounded-3xl border border-border/70 bg-card p-6 shadow-soft">
              <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-primary/10"></div>
              <div class="relative flex items-center gap-4">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-primary text-lg font-semibold text-primary-foreground">{{ $item['step'] }}</span>
                <div>
                  <h3 class="text-lg font-semibold text-foreground">{{ $item['title'] }}</h3>
                  <p class="mt-2 text-sm leading-relaxed text-muted-foreground">{{ $item['description'] }}</p>
                </div>
              </div>
            </article>
          @endforeach
        </div>
        <div class="mt-2 flex items-center justify-between pr-2">
          <button type="button" data-slider-prev aria-label="Попередній слайд" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-border bg-background text-sm font-medium text-muted-foreground transition disabled:opacity-40">‹</button>
          <div class="flex items-center gap-2" data-slider-dots></div>
          <button type="button" data-slider-next aria-label="Наступний слайд" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-border bg-background text-sm font-medium text-muted-foreground transition disabled:opacity-40">›</button>
        </div>
      </div>
    </div>
    <ol class="hidden gap-6 md:grid md:grid-cols-2" data-animate data-animate-delay="220">
      @foreach ($workflow as $item)
        <li class="group relative overflow-hidden rounded-3xl border border-border/70 bg-card p-6 shadow-soft transition hover:-translate-y-2 hover:border-primary/60 hover:shadow-xl">
          <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-primary/10 transition group-hover:scale-150"></div>
          <div class="relative flex items-center gap-4">
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-primary text-lg font-semibold text-primary-foreground">{{ $item['step'] }}</span>
            <div>
              <h3 class="text-lg font-semibold text-foreground">{{ $item['title'] }}</h3>
              <p class="mt-2 text-sm leading-relaxed text-muted-foreground">{{ $item['description'] }}</p>
            </div>
          </div>
        </li>
      @endforeach
    </ol>
  </section>

  <!-- AI TOOLKIT -->
  @php
    $aiToolkit = [
      ['title' => '💬 Пояснення відповідей', 'description' => 'AI автоматично формує короткі та зрозумілі пояснення після кожної вправи. Всі пояснення зберігаються у картці студента для подальшого аналізу.', 'icon' => 'M13 16h-1v-4h-1m1-4h.01M12 6a9 9 0 11-9 9 9 9 0 019-9z'],
      ['title' => '💡 Автоматичні підказки', 'description' => 'Під час проходження тесту студент може отримати контекстні підказки від AI без втрати темпу навчання. Система адаптується під рівень студента.', 'icon' => 'M4.5 12.75l6 6 9-13.5'],
      ['title' => '🎯 Визначення рівня', 'description' => 'Після завершення тесту Gramlyze автоматично визначає рівень CEFR та пропонує теми для повторення або поглиблення знань.', 'icon' => 'M12 8c-1.657 0-3 1.343-3 3 0 1.023.512 1.943 1.294 2.5l-1.36 3.543A1 1 0 009.868 18h4.264a1 1 0 00.934-1.457l-1.36-3.043A2.999 2.999 0 0015 11c0-1.657-1.343-3-3-3z'],
      ['title' => '📋 Рецензії запитань', 'description' => 'Зберігайте різні варіанти відповідей, типові помилки, коментарі від AI та створюйте персоналізовані плани навчання на їх основі.', 'icon' => 'M7 8h10M7 12h4m-4 4h6M5 5a2 2 0 012-2h10a2 2 0 012 2v14l-4-2-4 2-4-2-4 2z'],
    ];
  @endphp
  <section id="ai-toolkit" class="overflow-hidden rounded-[2.5rem] border border-border/80 bg-gradient-to-br from-background via-primary/5 to-secondary/10 p-10 shadow-soft md:p-16" data-animate>
    <div class="space-y-8">
      <div class="space-y-2" data-animate data-animate-delay="100">
        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-primary">🤖 AI Toolkit</p>
        <h2 class="text-3xl font-bold text-foreground md:text-4xl">ШІ як асистент, не заміна викладача</h2>
        <p class="max-w-2xl text-base leading-relaxed text-muted-foreground">Кожна функція AI допомагає зробити заняття змістовнішим та ефективнішим: Gramlyze аналізує, пропонує та фіксує результати, але рішення завжди приймає викладач.</p>
      </div>
      <div class="md:hidden" data-animate data-animate-delay="160">
        <div class="relative" data-slider>
          <div class="flex snap-x snap-mandatory gap-4 overflow-x-auto pb-8 pl-2" data-slider-track>
            @foreach ($aiToolkit as $tool)
              <article class="group relative min-w-[85%] basis-[85%] overflow-hidden rounded-3xl border border-border/70 bg-card p-6 shadow-soft">
                <div class="absolute -right-8 -top-8 h-28 w-28 rounded-full bg-primary/10"></div>
                <div class="relative space-y-4">
                  <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-primary/10 text-primary ring-1 ring-primary/20">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $tool['icon'] }}" />
                    </svg>
                  </span>
                  <h3 class="text-lg font-semibold text-foreground">{{ $tool['title'] }}</h3>
                  <p class="text-sm leading-relaxed text-muted-foreground">{{ $tool['description'] }}</p>
                </div>
              </article>
            @endforeach
          </div>
          <div class="mt-2 flex items-center justify-between pr-2">
            <button type="button" data-slider-prev aria-label="Попередній слайд" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-border bg-background text-sm font-medium text-muted-foreground transition disabled:opacity-40">‹</button>
            <div class="flex items-center gap-2" data-slider-dots></div>
            <button type="button" data-slider-next aria-label="Наступний слайд" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-border bg-background text-sm font-medium text-muted-foreground transition disabled:opacity-40">›</button>
          </div>
        </div>
      </div>
      <div class="hidden gap-6 md:grid md:grid-cols-2" data-animate data-animate-delay="220">
        @foreach ($aiToolkit as $tool)
          <article class="group relative overflow-hidden rounded-3xl border border-border/70 bg-card p-6 shadow-soft transition hover:-translate-y-2 hover:border-primary/60 hover:shadow-xl">
            <div class="absolute -right-8 -top-8 h-28 w-28 rounded-full bg-primary/10 transition group-hover:scale-150"></div>
            <div class="relative space-y-4">
              <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-primary/10 text-primary ring-1 ring-primary/20">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $tool['icon'] }}" />
                </svg>
              </span>
              <h3 class="text-lg font-semibold text-foreground">{{ $tool['title'] }}</h3>
              <p class="text-sm leading-relaxed text-muted-foreground">{{ $tool['description'] }}</p>
            </div>
          </article>
        @endforeach
      </div>
    </div>
  </section>

  <!-- TEAM USE -->
  @php
    $teamUseCases = [
      ['title' => '👤 Індивідуальні заняття', 'description' => 'Створюйте персональні навчальні плани з адаптивними рівнями складності. Зберігайте детальний прогрес і нотатки для кожного студента окремо.', 'color' => 'primary'],
      ['title' => '👥 Групові курси', 'description' => 'Організуйте повну бібліотеку занять для групи. Діліться тестами через загальну базу, аналізуйте типові помилки групи та індивідуальні досягнення.', 'color' => 'secondary'],
      ['title' => '🏢 Команди викладачів', 'description' => 'Спільні теги, історія всіх змін, швидке дублювання курсів та централізований банк навчальних матеріалів для всієї школи чи студії.', 'color' => 'accent'],
    ];
  @endphp
  <section id="team-collaboration" class="space-y-8" data-animate>
    <div class="space-y-2" data-animate data-animate-delay="100">
      <p class="text-xs font-semibold uppercase tracking-[0.35em] text-primary">🎭 Сценарії використання</p>
      <h2 class="text-3xl font-bold text-foreground md:text-4xl">Команда отримує спільний простір роботи</h2>
      <p class="max-w-2xl text-base leading-relaxed text-muted-foreground">Підключіть кількох викладачів, діліться готовими шаблонами, відстежуйте прогрес усіх груп — Gramlyze підтримує масштабування від індивідуального репетитора до великих мовних шкіл.</p>
    </div>
    <div class="md:hidden" data-animate data-animate-delay="160">
      <div class="relative" data-slider>
        <div class="flex snap-x snap-mandatory gap-4 overflow-x-auto pb-8 pl-2" data-slider-track>
          @foreach ($teamUseCases as $case)
            <article class="relative min-w-[80%] basis-[80%] rounded-3xl border border-border/70 bg-card p-6 shadow-soft">
              <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-{{ $case['color'] }}/10 text-{{ $case['color'] }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 014-4h4a4 4 0 014 4v2M9 7a4 4 0 118 0 4 4 0 01-8 0zm-6 10v-2a4 4 0 014-4h.01M3 7a4 4 0 108 0 4 4 0 00-8 0z" />
                </svg>
              </span>
              <h3 class="mt-4 text-lg font-semibold text-foreground">{{ $case['title'] }}</h3>
              <p class="mt-3 text-sm leading-relaxed text-muted-foreground">{{ $case['description'] }}</p>
            </article>
          @endforeach
        </div>
        <div class="mt-2 flex items-center justify-between pr-2">
          <button type="button" data-slider-prev aria-label="Попередній слайд" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-border bg-background text-sm font-medium text-muted-foreground transition disabled:opacity-40">‹</button>
          <div class="flex items-center gap-2" data-slider-dots></div>
          <button type="button" data-slider-next aria-label="Наступний слайд" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-border bg-background text-sm font-medium text-muted-foreground transition disabled:opacity-40">›</button>
        </div>
      </div>
    </div>
    <div class="hidden gap-6 md:grid md:grid-cols-3" data-animate data-animate-delay="220">
      @foreach ($teamUseCases as $case)
        <article class="rounded-3xl border border-border/70 bg-card p-6 shadow-soft">
          <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-{{ $case['color'] }}/10 text-{{ $case['color'] }}">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 014-4h4a4 4 0 014 4v2M9 7a4 4 0 118 0 4 4 0 01-8 0zm-6 10в-2a4 4 0 014-4h.01M3 7a4 4 0 108 0 4 4 0 00-8 0z" />
            </svg>
          </span>
          <h3 class="mt-4 text-lg font-semibold text-foreground">{{ $case['title'] }}</h3>
          <p class="mt-3 text-sm leading-relaxed text-muted-foreground">{{ $case['description'] }}</p>
        </article>
      @endforeach
    </div>
  </section>

  <!-- CTA -->
  <section class="overflow-hidden rounded-[2.5rem] border border-border/80 bg-gradient-to-br from-primary via-primary/80 to-secondary p-10 text-primary-foreground shadow-soft md:p-16" data-animate>
    <div class="grid gap-10 md:grid-cols-[1.5fr_1fr] md:items-center">
      <div class="space-y-6" data-animate data-animate-delay="100">
        <h2 class="text-3xl font-bold md:text-5xl">🚀 Готові протестувати Gramlyze з вашою командою?</h2>
        <p class="text-base leading-relaxed text-primary-foreground/95 md:text-lg">
          Приєднуйтесь до beta-доступу прямо зараз! Ми допоможемо вам мігрувати всі існуючі матеріали, налаштуємо оптимальну структуру тестів та надамо експертні поради щодо інтеграції AI у ваші навчальні програми.
        </p>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
          <a href="mailto:hello@gramlyze.com" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-background px-7 py-3.5 text-sm font-semibold text-foreground shadow-lg transition hover:bg-background/90 hover:shadow-xl">
            ✉️ Залишити заявку на доступ
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </a>
          <a href="{{ route('catalog-tests.cards') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border-2 border-primary-foreground/40 px-7 py-3.5 text-sm font-semibold text-primary-foreground/95 transition hover:border-primary-foreground/60 hover:bg-primary-foreground/10">
            👁️ Переглянути демо-каталог
          </a>
        </div>
      </div>
      <div class="space-y-4 rounded-3xl border border-primary-foreground/40 bg-primary-foreground/10 p-6 text-sm text-primary-foreground backdrop-blur" data-animate data-animate-delay="180">
        <div class="flex items-center gap-3">
          <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-background text-primary text-lg font-bold shadow-lg">1</span>
          <div>
            <p class="font-bold text-base">Виберіть формат роботи</p>
            <p class="text-primary-foreground/85">Індивідуальні уроки, групові заняття чи корпоративний формат навчання.</p>
          </div>
        </div>
        <div class="flex items-center gap-3">
          <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-background text-primary text-lg font-bold shadow-lg">2</span>
          <div>
            <p class="font-bold text-base">Міграція банку завдань</p>
            <p class="text-primary-foreground/85">Імпорт ваших існуючих вправ та матеріалів або створення бази з нуля.</p>
          </div>
        </div>
        <div class="flex items-center gap-3">
          <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-background text-primary text-lg font-bold shadow-lg">3</span>
          <div>
            <p class="font-bold text-base">Запуск навчальних потоків</p>
            <p class="text-primary-foreground/85">Отримайте персональний дашборд з прогресом учнів та AI-рекомендаціями.</p>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>
@endsection
