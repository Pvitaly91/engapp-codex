@extends('layouts.engram')

@section('title', 'Gramlyze — платформа англійської практики')

@section('content')
<div class="space-y-20">
  <!-- HERO -->
  <section id="hero" data-animate class="relative overflow-hidden rounded-[2.5rem] border border-border/80 bg-gradient-to-br from-primary/10 via-background to-secondary/10 p-10 shadow-soft md:p-14">
    <div class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_20%_15%,hsla(var(--accent),0.12),transparent_25%),radial-gradient(circle_at_80%_0%,hsla(var(--primary),0.14),transparent_30%)]"></div>
    <div class="grid gap-12 md:grid-cols-[1.35fr_1fr]">
      <div class="space-y-8" data-animate data-animate-delay="120">
        <span class="inline-flex items-center gap-2 rounded-full bg-background/70 px-5 py-1.5 text-xs font-semibold uppercase tracking-[0.4em] text-primary backdrop-blur">
          Новий публічний інтерфейс
        </span>
        <div class="space-y-5">
          <h1 class="text-4xl font-bold tracking-tight text-foreground md:text-6xl">
            Gramlyze: платформа для <span class="bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">викладачів англійської</span>
          </h1>
          <p class="text-base leading-relaxed text-muted-foreground md:text-xl max-w-2xl">
            Оновлений дизайн головної та всіх публічних сторінок: чіткі CTA, швидкий пошук, теми й теги під рукою. Створюйте уроки швидше, керуйте контентом упевненіше.
          </p>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
          <a href="{{ route('catalog-tests.cards') }}" class="group inline-flex items-center justify-center gap-2 rounded-2xl bg-primary px-7 py-3.5 text-sm font-semibold text-primary-foreground shadow-lg transition hover:-translate-y-0.5 hover:shadow-xl">
            📚 До каталогу тестів
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
        <div class="grid gap-4 sm:grid-cols-3">
          @php
            $stats = [
              ['label' => 'Категорій за CEFR та темами', 'value' => '120+'],
              ['label' => 'AI-підказок та рецензій', 'value' => '2 400+'],
              ['label' => 'Теги та ресурси в бібліотеці', 'value' => '7 500+'],
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
          <p class="text-xs font-semibold uppercase tracking-[0.3em] text-primary">Що змінилось</p>
          <h2 class="text-2xl font-semibold text-foreground">Новий публічний layout</h2>
          <p class="text-sm leading-relaxed text-muted-foreground">Хедер з CTA, швидкий пошук, оновлена палітра та полегшений футер. Доступно на головній, пошуку, каталозі та сторінках теорії.</p>
        </div>
        <dl class="space-y-3 text-sm text-muted-foreground">
          <div class="flex items-start gap-3 rounded-2xl border border-dashed border-primary/40 bg-primary/5 p-4">
            <span class="mt-1 h-2.5 w-2.5 rounded-full bg-primary"></span>
            <div>
              <dt class="font-semibold text-foreground">Єдиний каркас</dt>
              <dd>Шапка, пошук та футер тепер спільні для всіх публічних сторінок: менше відволікань, більше швидких дій.</dd>
            </div>
          </div>
          <div class="flex items-start gap-3 rounded-2xl border border-border/80 bg-background/80 p-4">
            <span class="mt-1 h-2.5 w-2.5 rounded-full bg-secondary"></span>
            <div>
              <dt class="font-semibold text-foreground">Видимі CTA</dt>
              <dd>Кнопки на каталог і конструктор тестів винесені у хедер та герой, щоб користувачі відразу могли почати роботу.</dd>
            </div>
          </div>
          <div class="flex items-start gap-3 rounded-2xl border border-border/80 bg-background/80 p-4">
            <span class="mt-1 h-2.5 w-2.5 rounded-full bg-accent"></span>
            <div>
              <dt class="font-semibold text-foreground">Темна тема</dt>
              <dd>Зберігається в локальному сховищі й доступна з футера: зручно для вечірньої підготовки уроків.</dd>
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
        'title' => 'Каталог тестів',
        'description' => 'Готові картки за CEFR, часовими формами та професійними сценаріями. Фільтри за тегами скорочують підготовку уроку.',
        'link' => route('catalog-tests.cards'),
        'accent' => 'primary',
        'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
      ],
      [
        'title' => 'Конструктор тестів',
        'description' => 'Створюйте кроки вручну або за шаблоном, додавайте AI-пояснення та миттєво діліться PDF.',
        'link' => route('grammar-test'),
        'accent' => 'secondary',
        'icon' => 'M4 6h16M4 10h16M4 14h16M4 18h7',
      ],
      [
        'title' => 'Теоретичні сторінки',
        'description' => 'Конспекти граматики та лексики українською. Внутрішні теги пов’язують теорію з вправами.',
        'link' => route('pages.index'),
        'accent' => 'accent',
        'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13',
      ],
      [
        'title' => 'Рецензії та аналіз',
        'description' => 'AI-перевірка відповідей і пояснення помилок. Слідкуйте за прогресом студентів у спільному просторі.',
        'link' => route('question-review.index'),
        'accent' => 'success',
        'icon' => 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z',
      ],
    ];
  @endphp
  <section id="solutions" class="space-y-8" data-animate>
    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between" data-animate data-animate-delay="80">
      <div class="space-y-2">
        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-primary">Мапа продукту</p>
        <h2 class="text-3xl font-bold text-foreground md:text-4xl">Публічні модулі Gramlyze</h2>
        <p class="max-w-2xl text-base leading-relaxed text-muted-foreground">Кожен модуль тепер відкривається в єдиному layout: навігація, CTA та пошук лишаються послідовними, незалежно від сторінки.</p>
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
            Перейти
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
      <p class="text-xs font-semibold uppercase tracking-[0.35em] text-primary">Командні процеси</p>
      <h2 class="text-3xl font-bold text-foreground md:text-4xl">Як працює новий публічний досвід</h2>
      <p class="max-w-3xl text-base leading-relaxed text-muted-foreground">Від першого візиту до публікації тестів — кожен крок має фіксований навігаційний блок і зрозумілі CTA. Менше кліків, більше зосередженості на методиці.</p>
    </div>
    <div class="grid gap-5 md:grid-cols-3" data-animate data-animate-delay="100">
      @php
        $steps = [
          ['title' => 'Знайти ресурс', 'body' => 'Швидкий пошук у хедері працює на всіх публічних сторінках: теги, теорія, каталоги.', 'icon' => 'M8 16l-4-4m0 0l4-4m-4 4h18'],
          ['title' => 'Почати урок', 'body' => 'CTA на каталог і конструктор тестів доступні одразу: стартуйте без додаткових переходів.', 'icon' => 'M12 4v16m8-8H4'],
          ['title' => 'Поділитися', 'body' => 'Оновлений футер з чіпами безпеки та підтримки: студенти бачать ключові маркери надійності.', 'icon' => 'M5 13l4 4L19 7'],
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
        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-primary">AI toolkit</p>
        <h2 class="text-3xl font-bold text-foreground md:text-4xl">AI-підказки тепер підсвічені</h2>
        <p class="max-w-2xl text-base leading-relaxed text-muted-foreground">Окремий блок для AI-можливостей на головній: користувачі бачать, як пояснення і рецензії працюють разом з каталогом.</p>
      </div>
      <a href="{{ route('question-review.index') }}" class="inline-flex items-center gap-2 rounded-full border border-border px-5 py-2 text-sm font-semibold text-foreground transition hover:border-primary hover:text-primary">Дивитися рецензії</a>
    </div>
    <div class="grid gap-6 md:grid-cols-[1.1fr_1fr]">
      <div class="rounded-3xl border border-border/70 bg-card p-6 shadow-soft space-y-4">
        <div class="inline-flex items-center gap-2 rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">AI + каталоги</div>
        <p class="text-lg font-semibold text-foreground">Підсвітка AI-функцій у публічному layout</p>
        <p class="text-sm leading-relaxed text-muted-foreground">Користувачі бачать, що рецензії, пояснення і визначення рівня доступні без зайвих переходів. Всі кнопки ведуть на відповідні модулі одразу.</p>
        <ul class="space-y-2 text-sm text-muted-foreground">
          <li class="flex items-start gap-2"><span class="mt-1 h-1.5 w-1.5 rounded-full bg-primary"></span>AI-пояснення та підказки закріплені у блоках CTA.</li>
          <li class="flex items-start gap-2"><span class="mt-1 h-1.5 w-1.5 rounded-full bg-secondary"></span>Прив'язка до тегів: AI бачить контекст теорії й каталогу.</li>
          <li class="flex items-start gap-2"><span class="mt-1 h-1.5 w-1.5 rounded-full bg-accent"></span>Використовуйте темну тему для нічних сесій — перемикач у футері.</li>
        </ul>
      </div>
      <div class="rounded-3xl border border-dashed border-primary/40 bg-primary/5 p-6 shadow-inner space-y-4">
        <div class="flex items-center gap-3">
          <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-primary text-white">⚡</span>
          <div>
            <p class="text-sm font-semibold text-primary">Єдиний досвід</p>
            <p class="text-xs text-muted-foreground">Одна шапка, один футер, одна палітра.</p>
          </div>
        </div>
        <div class="grid gap-3 sm:grid-cols-2">
          <div class="rounded-2xl border border-border bg-background/80 p-4 text-sm">
            <p class="font-semibold text-foreground">Швидкий старт</p>
            <p class="mt-1 text-muted-foreground">CTA та пошук доступні з першого екрану.</p>
          </div>
          <div class="rounded-2xl border border-border bg-background/80 p-4 text-sm">
            <p class="font-semibold text-foreground">Видима структура</p>
            <p class="mt-1 text-muted-foreground">Послідовність хедера/футера на всіх публічних сторінках.</p>
          </div>
        </div>
        <div class="rounded-2xl border border-border bg-background/90 p-4 text-sm">
          <p class="font-semibold text-foreground">Командний контроль</p>
          <p class="mt-1 text-muted-foreground">Додано чіпи про безпеку, підтримку та швидкий доступ до адмінки.</p>
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
        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-primary">Готові працювати</p>
        <h2 class="text-3xl font-bold text-foreground md:text-4xl">Спробуйте оновлений публічний досвід Gramlyze</h2>
        <p class="text-base leading-relaxed text-muted-foreground max-w-2xl">Почніть з каталогу, зберіть власний тест або відкрийте теоретичні сторінки. Всі переходи й стилі вже узгоджені.</p>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
          <a href="{{ route('catalog-tests.cards') }}" class="inline-flex items-center justify-center gap-2 rounded-full bg-primary px-6 py-3 text-sm font-semibold text-white shadow-lg transition hover:-translate-y-0.5 hover:shadow-xl">Відкрити каталог</a>
          <a href="{{ route('pages.index') }}" class="inline-flex items-center justify-center gap-2 rounded-full border border-border px-6 py-3 text-sm font-semibold text-foreground transition hover:border-primary hover:text-primary">Перейти до теорії</a>
        </div>
      </div>
      <div class="relative rounded-2xl border border-border/80 bg-card/90 p-6 shadow-lg">
        <div class="flex items-center gap-3">
          <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-primary text-white">🚀</span>
          <div>
            <p class="text-sm font-semibold text-foreground">Фокус на публічних сторінках</p>
            <p class="text-xs text-muted-foreground">/ (головна), /catalog, /pages, /search, /question-review</p>
          </div>
        </div>
        <p class="mt-4 text-sm leading-relaxed text-muted-foreground">Єдиний layout для всіх нефронтових маршрутів без /admin: користувачі швидко орієнтуються, а команда має спільні UI-патерни.</p>
      </div>
    </div>
  </section>
</div>
@endsection
