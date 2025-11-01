@extends('layouts.engram')

@section('title', 'Engram — платформа англійської практики')

@section('content')
<div class="space-y-16">
  <section class="relative overflow-hidden rounded-3xl border border-border/80 bg-gradient-to-br from-primary/10 via-background to-secondary/10 shadow-soft">
    <div class="absolute -left-24 top-10 h-56 w-56 rounded-full bg-primary/20 blur-3xl"></div>
    <div class="absolute -right-24 bottom-0 h-64 w-64 rounded-full bg-secondary/20 blur-3xl"></div>
    <div class="relative grid gap-10 px-6 py-12 md:grid-cols-2 md:px-12">
      <div class="space-y-6">
        <span class="inline-flex items-center rounded-full bg-background/80 px-3 py-1 text-xs font-semibold uppercase tracking-widest text-primary shadow-sm ring-1 ring-primary/30">beta доступ</span>
        <div class="space-y-3">
          <h1 class="text-3xl font-bold tracking-tight text-foreground md:text-4xl">Усе, що потрібно для граматики та практики англійської — в одному місці</h1>
          <p class="text-base text-muted-foreground md:text-lg">Engram об’єднує конструктор граматичних тестів, бібліотеку готових добірок, теоретичні статті та інструменти з підказками ШІ. Створено для викладачів та команд, які швидко готують якісний контент.</p>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
          <a href="{{ route('catalog-tests.cards') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-primary px-6 py-3 text-sm font-semibold text-primary-foreground shadow-soft transition hover:bg-primary/90">
            Переглянути тести
            <span aria-hidden="true">→</span>
          </a>
          <a href="{{ route('grammar-test') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-border bg-background/80 px-6 py-3 text-sm font-semibold text-foreground transition hover:border-primary/60 hover:text-primary">
            Зібрати власний тест
          </a>
        </div>
        <dl class="grid gap-4 sm:grid-cols-2">
          <div class="rounded-2xl border border-border/70 bg-background/70 p-4">
            <dt class="flex items-center gap-2 text-sm font-semibold text-foreground"><span class="text-lg">🧠</span>Автоматизовані добірки</dt>
            <dd class="mt-2 text-sm text-muted-foreground">Готові сценарії уроків із зазначеними рівнями, темами та підказками до кожного питання.</dd>
          </div>
          <div class="rounded-2xl border border-border/70 bg-background/70 p-4">
            <dt class="flex items-center gap-2 text-sm font-semibold text-foreground"><span class="text-lg">✨</span>AI-підтримка</dt>
            <dd class="mt-2 text-sm text-muted-foreground">Швидке пояснення відповідей, визначення рівня складності та генерація підказок за один клік.</dd>
          </div>
        </dl>
      </div>
      <div class="flex items-center justify-center">
        <div class="w-full max-w-sm space-y-4 rounded-3xl border border-border/70 bg-background/90 p-6 shadow-soft backdrop-blur">
          <div class="space-y-1">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">Шлях користувача</p>
            <h2 class="text-xl font-semibold text-foreground">Як працює Engram</h2>
          </div>
          <ol class="space-y-3 text-sm text-muted-foreground">
            <li class="flex gap-3">
              <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary/10 font-semibold text-primary">1</span>
              <span>Обирайте тест з каталогу або згенеруйте власний під конкретний запит.</span>
            </li>
            <li class="flex gap-3">
              <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary/10 font-semibold text-primary">2</span>
              <span>Проходьте вправи, використовуючи підказки, пояснення та інструменти рецензування запитань.</span>
            </li>
            <li class="flex gap-3">
              <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary/10 font-semibold text-primary">3</span>
              <span>Зберігайте результати, позначайте рівні, теги та повертайтесь до них у будь-який момент.</span>
            </li>
          </ol>
          <div class="rounded-2xl border border-dashed border-primary/50 bg-primary/5 p-4 text-sm text-muted-foreground">
            <p class="font-semibold text-primary">Порада</p>
            <p class="mt-1">Скористайтеся пошуком, щоб миттєво знаходити сторінки з теорією та окремі тести за тегами.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  @php
    $statLabels = [
        'tests' => ['label' => 'Готових тестів', 'icon' => '📋'],
        'questions' => ['label' => 'Питань у базі', 'icon' => '❓'],
        'pages' => ['label' => 'Сторінок теорії', 'icon' => '📚'],
        'tags' => ['label' => 'Тегів для фільтрації', 'icon' => '🏷️'],
    ];
  @endphp
  <section aria-labelledby="stats-heading" class="space-y-6">
    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
      <div>
        <h2 id="stats-heading" class="text-2xl font-semibold text-foreground">Повна картина вашої бази</h2>
        <p class="text-sm text-muted-foreground">Статистика оновлюється автоматично, щойно ви додаєте нові питання або статті.</p>
      </div>
      <a href="{{ route('saved-tests.list') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-primary hover:text-primary/80">
        Переглянути всі тести<span aria-hidden="true">→</span>
      </a>
    </div>
    <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
      @foreach($statLabels as $key => $meta)
        <div class="rounded-2xl border border-border/70 bg-card p-5 text-sm shadow-soft">
          <dt class="flex items-center gap-2 text-muted-foreground"><span class="text-xl">{{ $meta['icon'] }}</span>{{ $meta['label'] }}</dt>
          <dd class="mt-3 text-3xl font-semibold text-foreground">{{ number_format($stats[$key] ?? 0, 0, ',', ' ') }}</dd>
        </div>
      @endforeach
    </dl>
  </section>

  <section aria-labelledby="features-heading" class="space-y-6">
    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
      <div>
        <h2 id="features-heading" class="text-2xl font-semibold text-foreground">Що всередині Engram</h2>
        <p class="text-sm text-muted-foreground">Набір інструментів для створення, проходження та аналізу англомовних матеріалів.</p>
      </div>
    </div>
    @php
      $featureCards = [
        [
          'title' => 'Каталог граматичних тестів',
          'description' => 'Добірки запитань за рівнями CEFR, часами та спеціальними темами. Фільтруйте за тегами й отримуйте готовий набір для уроку.',
          'icon' => '🗂️',
          'link' => route('catalog-tests.cards'),
          'cta' => 'Відкрити каталог',
        ],
        [
          'title' => 'Конструктор тестів',
          'description' => 'Збирайте власний тест: обирайте питання, додавайте AI-вправи, налаштовуйте рівень складності та кількість завдань.',
          'icon' => '⚙️',
          'link' => route('grammar-test'),
          'cta' => 'Створити тест',
        ],
        [
          'title' => 'Теоретичні сторінки',
          'description' => 'Стислі пояснення граматики з прикладами, таблицями та підказками українською. Зручно використовувати під час заняття.',
          'icon' => '📖',
          'link' => route('pages.index'),
          'cta' => 'Перейти до теорії',
        ],
        [
          'title' => 'Підтримка запитань та рецензій',
          'description' => 'Перевіряйте варіанти відповідей, отримуйте пояснення від ШІ та зберігайте рецензії для подальшого аналізу.',
          'icon' => '🔍',
          'link' => route('question-review.index'),
          'cta' => 'Відкрити рецензії',
        ],
      ];
    @endphp
    <div class="grid gap-4 md:grid-cols-2">
      @foreach($featureCards as $feature)
        <article class="group flex h-full flex-col justify-between rounded-3xl border border-border/80 bg-card p-6 shadow-soft transition hover:-translate-y-1 hover:border-primary/70">
          <div class="space-y-3">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-primary/10 text-xl">{{ $feature['icon'] }}</span>
            <h3 class="text-lg font-semibold text-foreground">{{ $feature['title'] }}</h3>
            <p class="text-sm text-muted-foreground">{{ $feature['description'] }}</p>
          </div>
          <a href="{{ $feature['link'] }}" class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-primary transition group-hover:text-primary/80">
            {{ $feature['cta'] }}<span aria-hidden="true">→</span>
          </a>
        </article>
      @endforeach
    </div>
  </section>

  @if($latestTests->isNotEmpty())
    <section aria-labelledby="latest-tests-heading" class="space-y-6">
      <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div>
          <h2 id="latest-tests-heading" class="text-2xl font-semibold text-foreground">Останні збережені тести</h2>
          <p class="text-sm text-muted-foreground">Нове поповнення бази — швидкий спосіб знайти свіже натхнення для уроку.</p>
        </div>
        <a href="{{ route('catalog-tests.cards') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-primary hover:text-primary/80">Переглянути весь каталог<span aria-hidden="true">→</span></a>
      </div>
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($latestTests as $test)
          <article class="flex h-full flex-col rounded-3xl border border-border/80 bg-card p-6 shadow-soft">
            <div class="space-y-3">
              <div class="text-xs text-muted-foreground">Оновлено {{ optional($test->updated_at)->format('d.m.Y') }}</div>
              <h3 class="text-lg font-semibold text-foreground">{{ $test->name }}</h3>
              @if($test->description)
                <p class="text-sm text-muted-foreground">{{ \Illuminate\Support\Str::limit(strip_tags($test->description), 140) }}</p>
              @endif
            </div>
            <dl class="mt-4 grid gap-3 text-sm text-muted-foreground">
              <div class="flex items-center justify-between">
                <dt>Кількість завдань</dt>
                <dd class="font-semibold text-foreground">{{ $test->question_links_count }}</dd>
              </div>
              @php
                $levels = collect($test->filters['levels'] ?? $test->filters['level'] ?? [])->filter()->unique()->values();
              @endphp
              @if($levels->isNotEmpty())
                <div class="flex items-center justify-between">
                  <dt>Рівні</dt>
                  <dd class="font-semibold text-foreground">{{ $levels->implode(', ') }}</dd>
                </div>
              @endif
            </dl>
            <a href="{{ route('saved-test.js', $test->slug) }}" class="mt-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-secondary px-4 py-2 text-sm font-semibold text-secondary-foreground transition hover:bg-secondary/90">
              Перейти до тесту
            </a>
          </article>
        @endforeach
      </div>
    </section>
  @endif

  @if($featuredCategories->isNotEmpty() || $recentPages->isNotEmpty())
    <section aria-labelledby="knowledge-heading" class="space-y-8">
      <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div>
          <h2 id="knowledge-heading" class="text-2xl font-semibold text-foreground">Теорія та матеріали</h2>
          <p class="text-sm text-muted-foreground">Підбірки пояснень, які легко переглядати під час уроку.</p>
        </div>
        <a href="{{ route('pages.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-primary hover:text-primary/80">Відкрити всі сторінки<span aria-hidden="true">→</span></a>
      </div>
      @if($featuredCategories->isNotEmpty())
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
          @foreach($featuredCategories as $category)
            <article class="rounded-3xl border border-border/80 bg-card p-5 shadow-soft">
              <h3 class="text-base font-semibold text-foreground">{{ $category->title }}</h3>
              <p class="mt-2 text-sm text-muted-foreground">{{ $category->pages_count }} матеріал(и/ів) · {{ strtoupper($category->language ?? 'uk') }}</p>
              <a href="{{ route('pages.category', $category->slug) }}" class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-primary hover:text-primary/80">Переглянути<span aria-hidden="true">→</span></a>
            </article>
          @endforeach
        </div>
      @endif
      @if($recentPages->isNotEmpty())
        <div class="grid gap-4 md:grid-cols-2">
          @foreach($recentPages as $page)
            <article class="flex h-full flex-col justify-between rounded-3xl border border-border/80 bg-card p-6 shadow-soft">
              <div class="space-y-3">
                <span class="inline-flex items-center rounded-full bg-muted px-3 py-1 text-xs font-semibold text-muted-foreground">{{ $page->category->title }}</span>
                <h3 class="text-lg font-semibold text-foreground">{{ $page->title }}</h3>
                <p class="text-sm text-muted-foreground">{{ \Illuminate\Support\Str::limit(strip_tags($page->text), 160) }}</p>
              </div>
              <a href="{{ route('pages.show', [$page->category->slug, $page->slug]) }}" class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-primary hover:text-primary/80">Читати далі<span aria-hidden="true">→</span></a>
            </article>
          @endforeach
        </div>
      @endif
    </section>
  @endif

  <section class="rounded-3xl border border-border/70 bg-secondary/10 p-8 text-center shadow-soft">
    <div class="mx-auto max-w-2xl space-y-4">
      <h2 class="text-2xl font-semibold text-foreground">Готові спробувати Engram у роботі?</h2>
      <p class="text-sm text-muted-foreground">Напишіть нам, щоб отримати доступ, презентувати платформу команді або поставити запитання.</p>
      <div class="flex flex-col items-center justify-center gap-3 sm:flex-row">
        <a href="mailto:hello@engram.app" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-primary px-6 py-3 text-sm font-semibold text-primary-foreground shadow-soft transition hover:bg-primary/90">Запросити демо</a>
        <a href="https://t.me/engram_app" class="inline-flex items-center gap-2 text-sm font-semibold text-primary hover:text-primary/80">Написати в Telegram<span aria-hidden="true">→</span></a>
      </div>
    </div>
  </section>
</div>
@endsection
