@extends('layouts.engram')

@section('title', 'Engram — платформа англійської практики')

@section('content')
<div class="space-y-24">
  <!-- Hero -->
  <section class="relative overflow-hidden rounded-3xl border border-border/80 bg-gradient-to-br from-primary/10 via-background to-secondary/10 shadow-soft">
    <div class="absolute -left-24 top-10 h-56 w-56 rounded-full bg-primary/10 blur-3xl"></div>
    <div class="absolute -right-24 bottom-0 h-64 w-64 rounded-full bg-secondary/10 blur-3xl"></div>
    <div class="relative grid gap-12 px-6 py-16 md:grid-cols-[3fr,2fr] md:px-16">
      <div class="space-y-8">
        <span class="inline-flex items-center gap-2 rounded-full bg-background/80 px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-primary shadow-sm ring-1 ring-primary/30 backdrop-blur-sm">
          <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
          </svg>
          Нова версія Engram
        </span>
        <div class="space-y-4">
          <h1 class="bg-gradient-to-r from-foreground via-primary to-foreground bg-clip-text text-3xl font-bold tracking-tight text-transparent md:text-5xl">
            Побудуйте повний цикл підготовки англійських уроків за години, а не тижні
          </h1>
          <p class="max-w-xl text-base leading-relaxed text-muted-foreground md:text-lg">
            Гнучкі тести, бібліотека теорії, перевірка та рев’ю запитань із підказками ШІ. Engram дає командам викладачів єдину платформу для створення та проведення занять.
          </p>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
          <a href="{{ route('catalog-tests.cards') }}" class="group inline-flex items-center justify-center gap-2 rounded-2xl bg-primary px-6 py-3.5 text-sm font-semibold text-primary-foreground shadow-lg transition hover:scale-105 hover:shadow-xl">
            Переглянути каталог тестів
            <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
          </a>
          <a href="{{ route('grammar-test') }}" class="group inline-flex items-center justify-center gap-2 rounded-2xl border-2 border-border bg-background/80 px-6 py-3.5 text-sm font-semibold text-foreground backdrop-blur-sm transition hover:border-primary hover:text-primary">
            Згенерувати власний тест
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
            <dd class="mt-2 text-sm leading-relaxed text-muted-foreground">Готові уроки з визначеним рівнем, темою та поясненнями до кожного питання.</dd>
          </div>
          <div class="group rounded-2xl border border-border/70 bg-background/70 p-5 backdrop-blur-sm transition hover:-translate-y-1 hover:border-primary/50 hover:shadow-md">
            <dt class="flex items-center gap-2 text-sm font-semibold text-foreground">
              <svg class="h-5 w-5 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
              </svg>
              ШІ-помічник викладача
            </dt>
            <dd class="mt-2 text-sm leading-relaxed text-muted-foreground">Миттєві підказки, пояснення та визначення рівня складності прямо під час проходження тесту.</dd>
          </div>
        </dl>
      </div>
      <div class="relative flex items-center justify-center">
        <div class="w-full max-w-sm space-y-6 rounded-3xl border border-border/70 bg-background/90 p-7 shadow-xl backdrop-blur-md">
          <div class="space-y-2">
            <p class="flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
              </svg>
              Як Engram економить час
            </p>
            <h2 class="text-xl font-semibold text-foreground">Маршрут готового уроку</h2>
          </div>
          <ol class="space-y-4 text-sm text-muted-foreground">
            <li class="flex gap-3">
              <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-primary to-primary/70 font-semibold text-primary-foreground shadow-sm">1</span>
              <span class="leading-relaxed">Обирайте тематику з каталогу або згенеруйте персоналізований тест під конкретний запит.</span>
            </li>
            <li class="flex gap-3">
              <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-secondary to-secondary/70 font-semibold text-secondary-foreground shadow-sm">2</span>
              <span class="leading-relaxed">Супроводжуйте студентів підказками, автоперевіркою та поясненнями від ШІ.</span>
            </li>
            <li class="flex gap-3">
              <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-accent to-accent/70 font-semibold text-accent-foreground shadow-sm">3</span>
              <span class="leading-relaxed">Зберігайте результати, виставляйте рівні, позначайте теги й повертайтеся до матеріалів у будь-який момент.</span>
            </li>
          </ol>
          <div class="rounded-2xl border border-dashed border-primary/50 bg-primary/5 p-4 text-sm text-muted-foreground backdrop-blur-sm">
            <p class="flex items-center gap-2 font-semibold text-primary">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
              </svg>
              Порада
            </p>
            <p class="mt-1.5 leading-relaxed">Використовуйте пошук по сторінках, щоб швидко знайти теорію та приклади до будь-якої теми.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Snapshot stats -->
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
        <h2 id="stats-heading" class="text-3xl font-bold text-foreground">Ваша база знань, зібрана в цифрах</h2>
        <p class="text-sm text-muted-foreground">Статистика оновлюється автоматично після кожної публікації або імпорту питань.</p>
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

  <!-- Platform features -->
  <section aria-labelledby="features-heading" class="space-y-10">
    <div class="space-y-3 text-center">
      <h2 id="features-heading" class="text-3xl font-bold text-foreground">Що входить до платформи</h2>
      <p class="mx-auto max-w-2xl text-sm text-muted-foreground">Engram охоплює всі етапи роботи з англомовними матеріалами: від генерації контенту до перевірки знань та повторного використання напрацювань.</p>
    </div>
    @php
      $featureCards = [
        [
          'title' => 'Каталог граматичних тестів',
          'description' => 'Добірки запитань за рівнями CEFR, граматичними темами та професійними сценаріями. Швидкі фільтри й опис кожного тесту допоможуть підібрати потрібний набір.',
          'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
          'link' => route('catalog-tests.cards'),
          'cta' => 'Відкрити каталог',
          'accent' => ['badge' => 'bg-primary/10 text-primary', 'border' => 'hover:border-primary/50', 'link' => 'text-primary hover:text-primary/80'],
        ],
        [
          'title' => 'Конструктор власних тестів',
          'description' => 'Комбінуйте питання за тегами, рівнем і типом завдання. Додавайте власні питання або імпортуйте готові списки для створення персоналізованих уроків.',
          'icon' => 'M3 7h18M5 7v10a2 2 0 002 2h10a2 2 0 002-2V7M9 11h6m-6 4h6',
          'link' => route('grammar-test'),
          'cta' => 'Створити тест',
          'accent' => ['badge' => 'bg-secondary/10 text-secondary', 'border' => 'hover:border-secondary/50', 'link' => 'text-secondary hover:text-secondary/80'],
        ],
        [
          'title' => 'Пояснення та рев’ю запитань',
          'description' => 'Система рецензування дозволяє відстежувати проблемні запитання, додавати пояснення та спільно опрацьовувати відповіді зі студентами.',
          'icon' => 'M8 10h.01M12 14h.01M16 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
          'link' => route('question-review.index'),
          'cta' => 'Переглянути рев’ю',
          'accent' => ['badge' => 'bg-accent/10 text-accent', 'border' => 'hover:border-accent/50', 'link' => 'text-accent hover:text-accent/80'],
        ],
      ];
    @endphp
    <div class="grid gap-6 lg:grid-cols-3">
      @foreach($featureCards as $card)
        <div class="group relative overflow-hidden rounded-3xl border border-border/70 bg-card p-8 shadow-soft transition hover:-translate-y-2 {{ $card['accent']['border'] }}">
          <div class="absolute right-0 top-0 h-32 w-32 translate-x-10 -translate-y-10 rounded-full {{ $card['accent']['badge'] }} transition group-hover:scale-125 opacity-40"></div>
          <div class="relative space-y-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl {{ $card['accent']['badge'] }}">
              <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"/>
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-foreground">{{ $card['title'] }}</h3>
            <p class="text-sm text-muted-foreground">{{ $card['description'] }}</p>
            <a href="{{ $card['link'] }}" class="group inline-flex items-center gap-2 text-sm font-semibold {{ $card['accent']['link'] }} transition">
              {{ $card['cta'] }}
              <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
              </svg>
            </a>
          </div>
        </div>
      @endforeach
    </div>
  </section>

  <!-- Workflow section -->
  <section aria-labelledby="workflow-heading" class="space-y-8">
    <div class="grid gap-8 md:grid-cols-[2fr,3fr] md:items-center">
      <div class="space-y-4">
        <h2 id="workflow-heading" class="text-3xl font-bold text-foreground">Працюйте командою й контролюйте якість контенту</h2>
        <p class="text-sm text-muted-foreground">Engram допомагає розподіляти ролі між авторами, редакторами та викладачами. Будуйте процеси, де кожен етап зафіксований і прозорий.</p>
        <ul class="space-y-4 text-sm text-muted-foreground">
          <li class="flex items-start gap-3">
            <span class="mt-1 flex h-6 w-6 items-center justify-center rounded-full bg-primary/10 text-primary">1</span>
            <span class="leading-relaxed"><strong class="text-foreground">Планування уроків.</strong> Фіксуйте теми, рівні та бажану кількість питань — система підкаже наявні матеріали й прогалини.</span>
          </li>
          <li class="flex items-start gap-3">
            <span class="mt-1 flex h-6 w-6 items-center justify-center rounded-full bg-secondary/10 text-secondary">2</span>
            <span class="leading-relaxed"><strong class="text-foreground">Спільне редагування.</strong> Рецензуйте питання, додавайте пояснення та версії. Коментарі зберігаються, щоб команда бачила історію змін.</span>
          </li>
          <li class="flex items-start gap-3">
            <span class="mt-1 flex h-6 w-6 items-center justify-center rounded-full bg-accent/10 text-accent">3</span>
            <span class="leading-relaxed"><strong class="text-foreground">Аналіз результатів.</strong> Відстежуйте успішність студентів, зберігайте прогрес та створюйте повторні тренування за слабкими темами.</span>
          </li>
        </ul>
      </div>
      <div class="grid gap-4 rounded-3xl border border-border/80 bg-background/70 p-6 shadow-soft backdrop-blur">
        <div class="rounded-2xl border border-dashed border-primary/40 bg-primary/5 p-6">
          <h3 class="text-lg font-semibold text-foreground">Робочі простори</h3>
          <p class="mt-2 text-sm text-muted-foreground">Організуйте матеріали за напрямами: корпоративне навчання, підготовка до іспитів, приватні курси.</p>
        </div>
        <div class="rounded-2xl border border-dashed border-secondary/40 bg-secondary/5 p-6">
          <h3 class="text-lg font-semibold text-foreground">Контроль якості</h3>
          <p class="mt-2 text-sm text-muted-foreground">Використовуйте етикетки «Потребує рев’ю» і «Готово до уроку», щоб нічого не загубилося.</p>
        </div>
        <div class="rounded-2xl border border-dashed border-accent/40 bg-accent/5 p-6">
          <h3 class="text-lg font-semibold text-foreground">Готові сценарії</h3>
          <p class="mt-2 text-sm text-muted-foreground">Зберігайте кращі комбінації тестів та повертайтеся до них за кілька кліків.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Knowledge base -->
  <section aria-labelledby="knowledge-heading" class="space-y-8">
    <div class="space-y-2 text-center">
      <h2 id="knowledge-heading" class="text-3xl font-bold text-foreground">Теоретична база та додаткові ресурси</h2>
      <p class="mx-auto max-w-2xl text-sm text-muted-foreground">Доступ до повних теоретичних статей, перекладних завдань та словникових тренажерів — усе в єдиній системі.</p>
    </div>
    <div class="grid gap-6 md:grid-cols-3">
      <a href="{{ route('pages.index') }}" class="group rounded-3xl border border-border/70 bg-card p-8 shadow-soft transition hover:-translate-y-2 hover:border-primary/50">
        <div class="space-y-4">
          <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-primary/10 text-primary">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
          </div>
          <h3 class="text-xl font-semibold text-foreground">Бібліотека теорії</h3>
          <p class="text-sm text-muted-foreground">Структуровані статті з прикладами й поясненнями. Вбудовані блоки дозволяють додавати власні матеріали.</p>
          <span class="inline-flex items-center gap-2 text-sm font-semibold text-primary transition group-hover:text-primary/80">
            Перейти до сторінок
            <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
          </span>
        </div>
      </a>
      <a href="{{ route('words.search') }}" class="group rounded-3xl border border-border/70 bg-card p-8 shadow-soft transition hover:-translate-y-2 hover:border-secondary/50">
        <div class="space-y-4">
          <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-secondary/10 text-secondary">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
          </div>
          <h3 class="text-xl font-semibold text-foreground">Словниковий помічник</h3>
          <p class="text-sm text-muted-foreground">Пошук словоформ, прикладів уживання та тестування словника для студентів різних рівнів.</p>
          <span class="inline-flex items-center gap-2 text-sm font-semibold text-secondary transition group-hover:text-secondary/80">
            Перейти до словника
            <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
          </span>
        </div>
      </a>
      <a href="{{ route('translate.test') }}" class="group rounded-3xl border border-border/70 bg-card p-8 shadow-soft transition hover:-translate-y-2 hover:border-accent/50">
        <div class="space-y-4">
          <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-accent/10 text-accent">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h6a2 2 0 012 2v3m0 0h3a2 2 0 012 2v9a2 2 0 01-2 2H9a2 2 0 01-2-2v-3m0 0H4a1 1 0 01-1-1V6a1 1 0 011-1h3m0 9l3 3m0 0l3-3m-3 3V9"/>
            </svg>
          </div>
          <h3 class="text-xl font-semibold text-foreground">Перекладні та розмовні вправи</h3>
          <p class="text-sm text-muted-foreground">Практикуйте живі діалоги та переклад речень. Слідкуйте за прогресом і вбудовуйте вправи у свої курси.</p>
          <span class="inline-flex items-center gap-2 text-sm font-semibold text-accent transition group-hover:text-accent/80">
            Перейти до перекладу
            <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
          </span>
        </div>
      </a>
    </div>
  </section>

  <!-- Testimonials -->
  <section aria-labelledby="feedback-heading" class="space-y-10 rounded-3xl border border-border/70 bg-background/60 p-8 shadow-soft">
    <div class="space-y-2 text-center">
      <h2 id="feedback-heading" class="text-3xl font-bold text-foreground">Що кажуть команди про Engram</h2>
      <p class="mx-auto max-w-2xl text-sm text-muted-foreground">Викладачі й методисти діляться результатами після переходу на платформу.</p>
    </div>
    <div class="grid gap-6 md:grid-cols-3">
      @foreach([
        ['quote' => 'За два тижні ми перенесли всю граматику в Engram і тепер маємо контрольовані сценарії для корпоративних клієнтів.', 'role' => 'Методистка, школа корпоративної англійської'],
        ['quote' => 'Раніше витрачали години на рев’ю запитань. Тепер редактор бачить усю історію правок і коментарі в одному місці.', 'role' => 'Керівниця відділу якості контенту'],
        ['quote' => 'Студенти люблять підказки й пояснення, які можна отримати миттєво. Ми бачимо, на яких питаннях вони зупиняються.', 'role' => 'Викладач blended-курсу B2'],
      ] as $feedback)
        <figure class="rounded-2xl border border-border/70 bg-card p-6 shadow-sm">
          <blockquote class="text-sm text-foreground">“{{ $feedback['quote'] }}”</blockquote>
          <figcaption class="mt-4 text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">{{ $feedback['role'] }}</figcaption>
        </figure>
      @endforeach
    </div>
  </section>

  <!-- CTA -->
  <section class="relative overflow-hidden rounded-3xl border border-primary/40 bg-gradient-to-r from-primary/10 via-primary/20 to-secondary/20 p-10 text-center shadow-soft">
    <div class="absolute -top-24 left-1/2 h-64 w-64 -translate-x-1/2 rounded-full bg-primary/20 blur-3xl"></div>
    <div class="relative space-y-6">
      <h2 class="text-3xl font-bold text-foreground">Готові спробувати Engram у своїй команді?</h2>
      <p class="mx-auto max-w-2xl text-sm text-muted-foreground">Створіть перший тест, поділіться ним зі студентами та оцініть, як працює автоматична перевірка разом із поясненнями від ШІ.</p>
      <div class="flex flex-col items-center justify-center gap-3 sm:flex-row">
        <a href="{{ route('grammar-test') }}" class="group inline-flex items-center justify-center gap-2 rounded-2xl bg-primary px-6 py-3.5 text-sm font-semibold text-primary-foreground shadow-lg transition hover:scale-105 hover:shadow-xl">
          Розпочати безкоштовно
          <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
          </svg>
        </a>
        <a href="{{ route('saved-tests.list') }}" class="group inline-flex items-center justify-center gap-2 rounded-2xl border-2 border-border bg-background/80 px-6 py-3.5 text-sm font-semibold text-foreground backdrop-blur-sm transition hover:border-primary hover:text-primary">
          Переглянути наші добірки
          <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
          </svg>
        </a>
      </div>
    </div>
  </section>
</div>
@endsection
