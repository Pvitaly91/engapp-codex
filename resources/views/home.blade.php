@extends('layouts.engram')

@section('title', 'Engram — платформа для командної практики англійської')

@section('content')
<div class="space-y-24">
  <!-- HERO -->
  <section class="relative overflow-hidden rounded-3xl border border-border bg-gradient-to-br from-primary/10 via-background to-secondary/10">
    <div class="absolute -left-16 top-10 h-40 w-40 rounded-full bg-primary/20 blur-3xl"></div>
    <div class="absolute -right-20 bottom-0 h-52 w-52 rounded-full bg-secondary/20 blur-3xl"></div>
    <div class="absolute left-1/2 top-1/2 h-44 w-44 -translate-x-1/2 -translate-y-1/2 rounded-full bg-accent/10 blur-3xl"></div>

    <div class="relative grid gap-10 px-6 py-12 md:grid-cols-[1.2fr,1fr] md:px-12 md:py-16">
      <div class="space-y-8">
        <span class="inline-flex items-center gap-2 rounded-full bg-background/80 px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-primary ring-1 ring-primary/30">
          <span class="inline-flex h-2.5 w-2.5 rounded-full bg-primary"></span>
          Нове бачення навчання
        </span>
        <div class="space-y-4">
          <h1 class="text-3xl font-semibold tracking-tight text-foreground md:text-5xl">
            Бібліотека тестів, теорії та AI-помічників для команди викладачів
          </h1>
          <p class="text-base leading-relaxed text-muted-foreground md:text-lg">
            Engram поєднує інструменти підготовки завдань, контроль якості запитань та миттєві підказки від штучного інтелекту. Все в одній системі з продуманою аналітикою.
          </p>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
          <a href="{{ route('catalog-tests.cards') }}" class="group inline-flex items-center justify-center gap-2 rounded-2xl bg-primary px-6 py-3.5 text-sm font-semibold text-primary-foreground shadow-lg transition hover:-translate-y-0.5 hover:shadow-xl">
            Переглянути каталог
            <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </a>
          <a href="{{ route('grammar-test') }}" class="group inline-flex items-center justify-center gap-2 rounded-2xl border border-border bg-background/80 px-6 py-3.5 text-sm font-semibold text-foreground backdrop-blur transition hover:border-primary hover:text-primary">
            Створити власний тест
            <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </a>
        </div>
        <dl class="grid gap-4 sm:grid-cols-2">
          <div class="group rounded-2xl border border-border/70 bg-background/70 p-5 backdrop-blur-sm transition hover:-translate-y-1 hover:border-primary/60 hover:shadow-md">
            <dt class="flex items-center gap-3 text-sm font-semibold text-foreground">
              <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-primary/10 text-primary">AI</span>
              Розумні добірки
            </dt>
            <dd class="mt-2 text-sm leading-relaxed text-muted-foreground">Добирайте питання за рівнем, темою та форматом за лічені секунди.</dd>
          </div>
          <div class="group rounded-2xl border border-border/70 bg-background/70 p-5 backdrop-blur-sm transition hover:-translate-y-1 hover:border-secondary/60 hover:shadow-md">
            <dt class="flex items-center gap-3 text-sm font-semibold text-foreground">
              <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-secondary/10 text-secondary">∞</span>
              Командна робота
            </dt>
            <dd class="mt-2 text-sm leading-relaxed text-muted-foreground">Зберігайте тести, залишайте коментарі та повертайтесь до результатів, коли потрібно.</dd>
          </div>
        </dl>
      </div>
      <div class="flex items-center justify-center">
        <div class="w-full max-w-sm space-y-5 rounded-3xl border border-border/70 bg-background/90 p-8 shadow-soft backdrop-blur">
          <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-muted-foreground">Як ми ведемо користувача</p>
            <h2 class="text-xl font-semibold text-foreground">3 кроки до готового уроку</h2>
          </div>
          <ol class="space-y-4 text-sm text-muted-foreground">
            <li class="flex gap-3">
              <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-primary text-primary-foreground font-semibold">1</span>
              <span class="leading-relaxed">Почніть з каталогу або згенеруйте власний тест під конкретну потребу учня.</span>
            </li>
            <li class="flex gap-3">
              <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-secondary text-secondary-foreground font-semibold">2</span>
              <span class="leading-relaxed">Перевіряйте завдання, додавайте пояснення та AI-підказки без переходу між сервісами.</span>
            </li>
            <li class="flex gap-3">
              <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-accent text-accent-foreground font-semibold">3</span>
              <span class="leading-relaxed">Діліться результатами з командою, зберігайте їх для повторного використання та аналізу.</span>
            </li>
          </ol>
          <div class="rounded-2xl border border-dashed border-primary/50 bg-primary/5 p-4 text-sm text-muted-foreground">
            <p class="font-semibold text-primary">Миттєвий старт</p>
            <p>Перші результати з'являться вже після імпорту існуючих тестів — система автоматично проставить теги та рівні.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- PILLARS -->
  @php
    $pillars = [
      [
        'title' => 'Каталог тестів',
        'description' => 'Готові набори вправ за рівнем CEFR, граматичними темами та типом завдань.',
        'icon' => 'M3 5h12M9 3v2m0 4h6m-6 4h6m-6 4h6m-8 4h10a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z',
        'link' => route('catalog-tests.cards'),
      ],
      [
        'title' => 'Конструктор',
        'description' => 'Створюйте індивідуальні тести, обираючи питання з бази або додаючи власні.',
        'icon' => 'M12 6v6l4 2m6-2a10 10 0 11-20 0 10 10 0 0120 0z',
        'link' => route('grammar-test'),
      ],
      [
        'title' => 'База знань',
        'description' => 'Короткі пояснення, таблиці та приклади, згруповані за темами та категоріями.',
        'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13',
        'link' => route('pages.index'),
      ],
      [
        'title' => 'AI-помічники',
        'description' => 'Автоматичні пояснення, визначення рівня, генерація підказок та перевірка відповідей.',
        'icon' => 'M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707',
        'link' => route('ai-test.form'),
      ],
    ];
  @endphp
  <section aria-labelledby="pillars-heading" class="space-y-10">
    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
      <div class="space-y-2">
        <h2 id="pillars-heading" class="text-3xl font-bold text-foreground">Що отримує команда</h2>
        <p class="text-sm text-muted-foreground">Комбінуйте інструменти, щоб запустити повний цикл підготовки та проведення занять.</p>
      </div>
    </div>
    <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
      @foreach($pillars as $pillar)
        <article class="group relative flex h-full flex-col justify-between overflow-hidden rounded-3xl border border-border bg-card p-7 shadow-soft transition hover:-translate-y-1 hover:border-primary/60 hover:shadow-xl">
          <div class="absolute -right-8 -top-8 h-28 w-28 rounded-full bg-primary/5 transition group-hover:scale-150"></div>
          <div class="relative space-y-4">
            <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-primary/10 text-primary">
              <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $pillar['icon'] }}" />
              </svg>
            </span>
            <h3 class="text-lg font-semibold text-foreground">{{ $pillar['title'] }}</h3>
            <p class="text-sm leading-relaxed text-muted-foreground">{{ $pillar['description'] }}</p>
          </div>
          <a href="{{ $pillar['link'] }}" class="relative mt-6 inline-flex items-center gap-2 text-sm font-semibold text-primary transition group-hover:gap-3">
            Детальніше
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
      'tests' => ['label' => 'Готових тестів', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
      'questions' => ['label' => 'Питань у базі', 'icon' => 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
      'pages' => ['label' => 'Сторінок теорії', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
      'tags' => ['label' => 'Тегів для фільтрації', 'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z'],
    ];
  @endphp
  <section aria-labelledby="stats-heading" class="space-y-8">
    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
      <div class="space-y-2">
        <h2 id="stats-heading" class="text-3xl font-bold text-foreground">Актуальні показники бази</h2>
        <p class="text-sm text-muted-foreground">Статистика оновлюється автоматично після кожного нового питання чи сторінки.</p>
      </div>
      <a href="{{ route('saved-tests.list') }}" class="group inline-flex items-center gap-2 text-sm font-semibold text-primary transition hover:text-primary/80">
        Усі тести
        <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
        </svg>
      </a>
    </div>
    <dl class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
      @foreach($statLabels as $key => $meta)
        <div class="group relative overflow-hidden rounded-3xl border border-border bg-card p-6 shadow-soft transition hover:-translate-y-1 hover:border-primary/60 hover:shadow-xl">
          <div class="absolute right-0 top-0 h-24 w-24 translate-x-8 -translate-y-8 rounded-full bg-primary/5 transition group-hover:scale-150"></div>
          <dt class="relative flex items-center gap-3 text-sm font-medium text-muted-foreground">
            <svg class="h-6 w-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $meta['icon'] }}" />
            </svg>
            {{ $meta['label'] }}
          </dt>
          <dd class="relative mt-3 text-3xl font-bold text-foreground">{{ number_format($stats[$key] ?? 0, 0, ',', ' ') }}</dd>
        </div>
      @endforeach
    </dl>
  </section>

  <!-- LATEST TESTS -->
  <section aria-labelledby="latest-tests" class="space-y-8">
    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
      <div class="space-y-2">
        <h2 id="latest-tests" class="text-3xl font-bold text-foreground">Нещодавно створені тести</h2>
        <p class="text-sm text-muted-foreground">Добірки, які вже використовує команда. Поверніться до них або адаптуйте під нових студентів.</p>
      </div>
      <a href="{{ route('saved-tests.list') }}" class="group inline-flex items-center gap-2 text-sm font-semibold text-primary transition hover:text-primary/80">
        Дивитись усі
        <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
        </svg>
      </a>
    </div>
    <div class="grid gap-6 md:grid-cols-3">
      @forelse($latestTests as $test)
        <article class="flex h-full flex-col justify-between rounded-3xl border border-border bg-card p-6 shadow-soft transition hover:-translate-y-1 hover:border-secondary/60 hover:shadow-xl">
          <div class="space-y-3">
            <span class="inline-flex items-center gap-2 rounded-full bg-secondary/10 px-3 py-1 text-xs font-semibold text-secondary">
              {{ $test->level ?? 'Рівень не вказано' }}
            </span>
            <h3 class="text-lg font-semibold text-foreground line-clamp-2">{{ $test->title ?? 'Без назви' }}</h3>
            <p class="text-sm text-muted-foreground line-clamp-3">{{ $test->description ?? 'Опис з’явиться після редагування тесту.' }}</p>
          </div>
          <dl class="mt-6 flex items-center justify-between text-xs text-muted-foreground">
            <div class="flex items-center gap-2">
              <svg class="h-4 w-4 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16" />
              </svg>
              <span>{{ $test->question_links_count }} питань</span>
            </div>
            <span>{{ optional($test->updated_at ?? $test->created_at)->diffForHumans() }}</span>
          </dl>
        </article>
      @empty
        <div class="col-span-full rounded-3xl border border-dashed border-border bg-muted/30 p-10 text-center text-sm text-muted-foreground">
          Поки що немає збережених тестів. Створіть перший — і він з’явиться тут автоматично.
        </div>
      @endforelse
    </div>
  </section>

  <!-- KNOWLEDGE BASE -->
  <section aria-labelledby="knowledge-heading" class="space-y-8">
    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
      <div class="space-y-2">
        <h2 id="knowledge-heading" class="text-3xl font-bold text-foreground">З чого почати підготовку</h2>
        <p class="text-sm text-muted-foreground">Категорії з теоретичними сторінками та прикладами, які найчастіше оновлює команда.</p>
      </div>
      <a href="{{ route('pages.index') }}" class="group inline-flex items-center gap-2 text-sm font-semibold text-primary transition hover:text-primary/80">
        Уся база знань
        <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
        </svg>
      </a>
    </div>
    <div class="grid gap-6 md:grid-cols-2">
      @forelse($featuredCategories as $category)
        <article class="rounded-3xl border border-border bg-card p-7 shadow-soft transition hover:-translate-y-1 hover:border-accent/60 hover:shadow-xl">
          <div class="flex items-start justify-between gap-4">
            <div class="space-y-3">
              <h3 class="text-lg font-semibold text-foreground">{{ $category->title }}</h3>
              <p class="text-sm text-muted-foreground">{{ $category->description ?? 'Категорія очікує опису, але вже містить корисні сторінки.' }}</p>
            </div>
            <span class="rounded-full bg-accent/10 px-3 py-1 text-xs font-semibold text-accent">{{ $category->pages_count }} стор.</span>
          </div>
        </article>
      @empty
        <div class="col-span-full rounded-3xl border border-dashed border-border bg-muted/30 p-10 text-center text-sm text-muted-foreground">
          Додайте перші сторінки теорії, щоб швидко ділитися матеріалами з командою.
        </div>
      @endforelse
    </div>
  </section>

  <!-- RECENT ARTICLES -->
  <section aria-labelledby="articles-heading" class="space-y-8">
    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
      <div class="space-y-2">
        <h2 id="articles-heading" class="text-3xl font-bold text-foreground">Останні оновлення бази знань</h2>
        <p class="text-sm text-muted-foreground">Перегляньте, які матеріали нещодавно редагувалися, щоб бути в курсі змін.</p>
      </div>
    </div>
    <div class="grid gap-6 lg:grid-cols-2">
      @forelse($recentPages as $page)
        <article class="rounded-3xl border border-border bg-card p-6 shadow-soft transition hover:-translate-y-1 hover:border-info/60 hover:shadow-xl">
          <div class="space-y-3">
            <div class="flex items-center gap-2 text-xs text-muted-foreground">
              <span class="inline-flex items-center gap-1 rounded-full bg-muted px-2 py-1">
                <svg class="h-3.5 w-3.5 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3" />
                </svg>
                {{ optional($page->updated_at ?? $page->created_at)->diffForHumans() }}
              </span>
              <span>•</span>
              <span>{{ $page->category?->title ?? 'Без категорії' }}</span>
            </div>
            <h3 class="text-lg font-semibold text-foreground">{{ $page->title }}</h3>
            <p class="text-sm text-muted-foreground line-clamp-3">{{ $page->description ?? 'Опис ще не додано, але матеріал уже доступний для команди.' }}</p>
          </div>
          <a href="{{ route('pages.show', [$page->category?->slug, $page->slug]) }}" class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-info transition hover:text-info/80">
            Перейти до сторінки
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </a>
        </article>
      @empty
        <div class="col-span-full rounded-3xl border border-dashed border-border bg-muted/30 p-10 text-center text-sm text-muted-foreground">
          Ще немає опублікованих сторінок. Додайте перший матеріал, і він відобразиться в цьому списку.
        </div>
      @endforelse
    </div>
  </section>

  <!-- CTA -->
  <section class="overflow-hidden rounded-3xl border border-border bg-gradient-to-r from-primary/90 to-secondary/90 text-primary-foreground">
    <div class="grid gap-6 px-8 py-12 md:grid-cols-[1.2fr,1fr] md:px-12">
      <div class="space-y-4">
        <span class="inline-flex items-center gap-2 rounded-full bg-primary-foreground/10 px-3 py-1 text-xs font-semibold uppercase tracking-widest">
          Готові масштабуватися
        </span>
        <h2 class="text-3xl font-semibold md:text-4xl">Запросіть команду та налаштуйте ролі за кілька хвилин</h2>
        <p class="text-sm md:text-base text-primary-foreground/80">Додавайте викладачів, розподіляйте доступ до тестів, сторінок та AI-інструментів — Engram підтримує спільну роботу без зайвої бюрократії.</p>
      </div>
      <div class="flex items-center justify-end">
        <a href="{{ route('login.show') }}" class="inline-flex items-center justify-center rounded-2xl bg-background px-6 py-3 text-sm font-semibold text-foreground shadow-lg transition hover:-translate-y-0.5 hover:shadow-xl">
          Увійти або зареєструватися
        </a>
      </div>
    </div>
  </section>
</div>
@endsection
