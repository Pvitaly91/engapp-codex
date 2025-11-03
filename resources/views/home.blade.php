@extends('layouts.engram')

@section('title', 'Gramlyze — платформа англійської практики')

@section('content')
<div class="space-y-24">
  <!-- HERO -->
  <section id="hero" class="relative overflow-hidden rounded-[2.5rem] border border-border/80 bg-gradient-to-br from-primary/10 via-background to-secondary/15 p-10 shadow-soft md:p-16">
    <div class="absolute -top-32 right-10 h-64 w-64 rounded-full bg-primary/20 blur-3xl"></div>
    <div class="absolute -bottom-32 left-4 h-72 w-72 rounded-full bg-accent/15 blur-3xl"></div>
    <div class="relative grid gap-12 md:grid-cols-[1.35fr_1fr]">
      <div class="space-y-8">
        <span class="inline-flex items-center gap-2 rounded-full bg-background/70 px-5 py-1.5 text-xs font-semibold uppercase tracking-[0.4em] text-primary backdrop-blur">
          beta доступ
        </span>
        <div class="space-y-6">
          <h1 class="text-3xl font-semibold tracking-tight text-foreground md:text-5xl">
            Конструктор тестів, теорія та AI-помічники для викладачів англійської
          </h1>
          <p class="text-base leading-relaxed text-muted-foreground md:text-lg">
            Gramlyze поєднує повний цикл підготовки до заняття: від пошуку завдань до перевірки відповідей і аналітики по студентах. Усе працює у браузері та синхронізується між командою.
          </p>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
          <a href="{{ route('catalog-tests.cards') }}" class="group inline-flex items-center justify-center gap-2 rounded-2xl bg-primary px-7 py-3.5 text-sm font-semibold text-primary-foreground shadow-lg transition hover:-translate-y-0.5 hover:shadow-xl">
            Перейти до каталогу
            <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </a>
          <a href="{{ route('grammar-test') }}" class="group inline-flex items-center justify-center gap-2 rounded-2xl border border-border bg-background/80 px-7 py-3.5 text-sm font-semibold text-foreground backdrop-blur transition hover:border-primary hover:text-primary">
            Зібрати власний тест
            <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </a>
        </div>
        @php
          $heroHighlights = [
            ['label' => 'Каталоги за CEFR та темами', 'icon' => 'M19 11H5M7 7h10M9 3h6a2 2 0 012 2v2H7V5a2 2 0 012-2zM5 11v6a2 2 0 002 2h10a2 2 0 002-2v-6'],
            ['label' => 'AI-перевірка відповідей', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['label' => 'Спільна робота команди', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2a3 3 0 00-.132-.894M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2a3 3 0 01.132-.894M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a3 3 0 11-6 0 3 3 0 016 0zM9 10a3 3 0 11-6 0 3 3 0 016 0z'],
          ];
        @endphp
        <dl class="grid gap-4 sm:grid-cols-3">
          @foreach ($heroHighlights as $item)
            <div class="group rounded-2xl border border-border/70 bg-background/80 p-4 backdrop-blur transition hover:-translate-y-1 hover:border-primary/50">
              <dt class="flex items-center gap-3 text-sm font-semibold text-foreground">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-primary/10 text-primary">
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

      <div class="space-y-6 rounded-3xl border border-border/60 bg-card/90 p-6 shadow-xl backdrop-blur">
        <div class="space-y-3">
          <p class="text-xs font-semibold uppercase tracking-[0.3em] text-muted-foreground">Як виглядає день з Gramlyze</p>
          <h2 class="text-2xl font-semibold text-foreground">3 модулі, що закривають підготовку уроку</h2>
        </div>
        <div class="space-y-4 text-sm text-muted-foreground">
          <p class="rounded-2xl border border-dashed border-primary/40 bg-primary/10 p-4 text-primary">
            Конструктор тестів аналізує банк питань, пропонує AI-переформулювання та одразу показує рекомендації з рівня.
          </p>
          <ul class="space-y-3">
            <li class="flex items-start gap-3">
              <span class="mt-1 h-2.5 w-2.5 rounded-full bg-primary"></span>
              <span>Каталоги з тегами дозволяють зібрати сценарій заняття за 5 хвилин.</span>
            </li>
            <li class="flex items-start gap-3">
              <span class="mt-1 h-2.5 w-2.5 rounded-full bg-secondary"></span>
              <span>AI-рецензії фіксують типові помилки та формують підсумкові пояснення для групи.</span>
            </li>
            <li class="flex items-start gap-3">
              <span class="mt-1 h-2.5 w-2.5 rounded-full bg-accent"></span>
              <span>Теорія та перекладні вправи доступні у спільній бібліотеці команди.</span>
            </li>
          </ul>
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
        'title' => 'Каталог тестів',
        'description' => 'Добірки завдань за рівнями, часами, професійними контекстами. Фільтруйте за тегами та обсягом.',
        'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
        'cta' => 'Відкрити каталог',
        'link' => route('catalog-tests.cards'),
        'accent' => 'primary'
      ],
      [
        'title' => 'Конструктор тестів',
        'description' => 'Збирайте власні вправи, додавайте AI-підказки, контролюйте складність і кількість кроків.',
        'icon' => 'M4 6h16M4 10h16M4 14h16M4 18h7',
        'cta' => 'Створити тест',
        'link' => route('grammar-test'),
        'accent' => 'secondary'
      ],
      [
        'title' => 'Теоретичні сторінки',
        'description' => 'Конспекти з прикладами, таблицями та швидкими нагадуваннями українською мовою.',
        'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13',
        'cta' => 'Перейти до теорії',
        'link' => route('pages.index'),
        'accent' => 'accent'
      ],
      [
        'title' => 'Рецензії та аналіз відповідей',
        'description' => 'AI-пояснення, порівняння варіантів, збереження коментарів для кожного студента чи групи.',
        'icon' => 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z',
        'cta' => 'Переглянути рецензії',
        'link' => route('question-review.index'),
        'accent' => 'success'
      ],
    ];
  @endphp
  <section id="solutions" class="space-y-8">
    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
      <div class="space-y-2">
        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-primary">Мапа платформи</p>
        <h2 class="text-3xl font-semibold text-foreground">Інструменти платформи Gramlyze</h2>
        <p class="max-w-2xl text-sm leading-relaxed text-muted-foreground">Кожний модуль взаємопов'язаний: теги з теорії доступні у тестах, AI-рекомендації видно у каталозі, а результати зберігаються у спільному просторі.</p>
      </div>
    </div>
    <div class="grid gap-6 md:grid-cols-2">
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
        'tests' => ['label' => 'Готових тестів', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
        'questions' => ['label' => 'Питань у базі', 'icon' => 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        'pages' => ['label' => 'Сторінок теорії', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
        'tags' => ['label' => 'Тегів для фільтрації', 'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z'],
    ];
  @endphp
  <section id="metrics" class="space-y-8">
    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
      <div class="space-y-2">
        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-primary">Дані вашої бази</p>
        <h2 class="text-3xl font-semibold text-foreground">Статистика оновлюється автоматично</h2>
        <p class="max-w-2xl text-sm leading-relaxed text-muted-foreground">Коли ви додаєте нові завдання або редагуєте теорію, показники перераховуються й доступні команді у реальному часі.</p>
      </div>
      <a href="{{ route('saved-tests.list') }}" class="group inline-flex items-center gap-2 self-start rounded-2xl border border-border px-4 py-2 text-sm font-semibold text-muted-foreground transition hover:border-primary hover:text-primary">
        Переглянути всі тести
        <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
        </svg>
      </a>
    </div>
    <dl class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
      @foreach ($statLabels as $key => $meta)
        <div class="group relative overflow-hidden rounded-3xl border border-border/70 bg-card p-6 shadow-soft transition hover:-translate-y-2 hover:shadow-xl">
          <div class="absolute right-0 top-0 h-28 w-28 translate-x-10 -translate-y-10 rounded-full bg-primary/10 transition group-hover:scale-150"></div>
          <dt class="relative flex items-center gap-3 text-sm font-medium text-muted-foreground">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-primary/10 text-primary">
              <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $meta['icon'] }}" />
              </svg>
            </span>
            {{ $meta['label'] }}
          </dt>
          <dd class="relative mt-5 text-4xl font-semibold tracking-tight text-foreground">{{ number_format($stats[$key] ?? 0, 0, ',', ' ') }}</dd>
        </div>
      @endforeach
    </dl>
  </section>

  <!-- WORKFLOW -->
  @php
    $workflow = [
      ['step' => '1', 'title' => 'Знайдіть потрібний набір завдань', 'description' => 'Фільтруйте каталог за рівнем, граматичною темою або професійним доменом. Збережіть шаблон для повторного використання.'],
      ['step' => '2', 'title' => 'Налаштуйте тест під конкретну групу', 'description' => 'Додавайте власні питання, налаштовуйте кількість кроків, додавайте AI-підказки та пояснення.'],
      ['step' => '3', 'title' => 'Ведіть урок та збирайте результати', 'description' => 'Діліться посиланням або експортуйте в PDF. Результати відстежуються, а AI-аналіз показує типові помилки.'],
      ['step' => '4', 'title' => 'Аналізуйте успішність', 'description' => 'Переглядайте рецензії, теги та нотатки команди, формуйте наступні добірки за рекомендаціями.'],
    ];
  @endphp
  <section id="workflow" class="space-y-8">
    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
      <div class="space-y-2">
        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-primary">Процес роботи</p>
        <h2 class="text-3xl font-semibold text-foreground">Від пошуку вправ до аналітики — за один потік</h2>
        <p class="max-w-2xl text-sm leading-relaxed text-muted-foreground">Gramlyze структурує робочий день викладача: ви не губитеся між Google-доками і таблицями, а працюєте в єдиній системі.</p>
      </div>
    </div>
    <ol class="grid gap-6 md:grid-cols-2">
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
      ['title' => 'Пояснення відповідей', 'description' => 'AI формує коротке пояснення після кожної вправи та зберігає його у картці студента.', 'icon' => 'M13 16h-1v-4h-1m1-4h.01M12 6a9 9 0 11-9 9 9 9 0 019-9z'],
      ['title' => 'Автоматичні підказки', 'description' => 'Під час тесту студент може отримати контекстні підказки й не втратити темп.', 'icon' => 'M4.5 12.75l6 6 9-13.5'],
      ['title' => 'Визначення рівня', 'description' => 'Після проходження тесту Gramlyze пропонує рівень CEFR і тему для повторення.', 'icon' => 'M12 8c-1.657 0-3 1.343-3 3 0 1.023.512 1.943 1.294 2.5l-1.36 3.543A1 1 0 009.868 18h4.264a1 1 0 00.934-1.457l-1.36-3.043A2.999 2.999 0 0015 11c0-1.657-1.343-3-3-3z'],
      ['title' => 'Рецензії запитань', 'description' => 'Зберігайте варіанти, помилки, коментарі ШІ і робіть на їх основі наступні плани.', 'icon' => 'M7 8h10M7 12h4m-4 4h6M5 5a2 2 0 012-2h10a2 2 0 012 2v14l-4-2-4 2-4-2-4 2z'],
    ];
  @endphp
  <section id="ai-toolkit" class="overflow-hidden rounded-[2.5rem] border border-border/80 bg-gradient-to-br from-background via-primary/5 to-secondary/10 p-10 shadow-soft md:p-16">
    <div class="space-y-8">
      <div class="space-y-2">
        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-primary">AI Toolkit</p>
        <h2 class="text-3xl font-semibold text-foreground">ШІ як асистент, а не заміна викладача</h2>
        <p class="max-w-2xl text-sm leading-relaxed text-muted-foreground">Кожна функція допомагає зробити заняття змістовнішим: Gramlyze аналізує, пропонує та фіксує результати, але рішення ухвалює викладач.</p>
      </div>
      <div class="grid gap-6 md:grid-cols-2">
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
      ['title' => 'Індивідуальні заняття', 'description' => 'Готуйте персональні плани з адаптивними рівнями складності, зберігайте прогрес і нотатки для кожного студента.', 'color' => 'primary'],
      ['title' => 'Групові курси', 'description' => 'Організуйте бібліотеку занять для групи, діліться тестами через загальну базу, аналізуйте типові помилки.', 'color' => 'secondary'],
      ['title' => 'Команди викладачів', 'description' => 'Спільні теги, історія змін, швидке дублювання курсів та централізований банк матеріалів.', 'color' => 'accent'],
    ];
  @endphp
  <section id="team-collaboration" class="space-y-8">
    <div class="space-y-2">
      <p class="text-xs font-semibold uppercase tracking-[0.35em] text-primary">Сценарії використання</p>
      <h2 class="text-3xl font-semibold text-foreground">Команда отримує спільний простір роботи</h2>
      <p class="max-w-2xl text-sm leading-relaxed text-muted-foreground">Підключіть кілька викладачів, діліться шаблонами, відстежуйте прогрес груп — Gramlyze підтримує масштабування студій та онлайн-шкіл.</p>
    </div>
    <div class="grid gap-6 md:grid-cols-3">
      @foreach ($teamUseCases as $case)
        <article class="rounded-3xl border border-border/70 bg-card p-6 shadow-soft">
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
  </section>

  <!-- CTA -->
  <section class="overflow-hidden rounded-[2.5rem] border border-border/80 bg-gradient-to-br from-primary via-primary/80 to-secondary p-10 text-primary-foreground shadow-soft md:p-16">
    <div class="grid gap-10 md:grid-cols-[1.5fr_1fr] md:items-center">
      <div class="space-y-6">
        <h2 class="text-3xl font-semibold md:text-4xl">Готові протестувати Gramlyze з командою?</h2>
        <p class="text-base leading-relaxed text-primary-foreground/90">
          Долучіться до beta-доступу: ми допоможемо мігрувати існуючі матеріали, налаштуємо структуру тестів та дамо поради щодо інтеграції AI у ваші програми.
        </p>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
          <a href="{{ route('login.show') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-background px-6 py-3 text-sm font-semibold text-foreground transition hover:bg-background/80">
            Увійти або зареєструватися
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </a>
          <a href="{{ route('catalog-tests.cards') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-primary-foreground/40 px-6 py-3 text-sm font-semibold text-primary-foreground/90 transition hover:bg-primary-foreground/10">
            Переглянути демо-каталог
          </a>
        </div>
      </div>
      <div class="space-y-4 rounded-3xl border border-primary-foreground/40 bg-primary-foreground/10 p-6 text-sm text-primary-foreground">
        <div class="flex items-center gap-3">
          <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-background text-primary font-semibold">1</span>
          <div>
            <p class="font-semibold">Виберіть формат роботи</p>
            <p class="text-primary-foreground/80">Індивідуальні уроки, групи чи корпоративний формат.</p>
          </div>
        </div>
        <div class="flex items-center gap-3">
          <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-background text-primary font-semibold">2</span>
          <div>
            <p class="font-semibold">Міграція банку завдань</p>
            <p class="text-primary-foreground/80">Імпорт ваших існуючих вправ або створення з нуля.</p>
          </div>
        </div>
        <div class="flex items-center gap-3">
          <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-background text-primary font-semibold">3</span>
          <div>
            <p class="font-semibold">Запуск навчальних потоків</p>
            <p class="text-primary-foreground/80">Отримайте дашборд з прогресом та AI-рекомендаціями.</p>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>
@endsection
