@extends('layouts.engram')

@section('title', 'Engram — платформа англійської практики')

@section('content')
<div class="space-y-20">
  <!-- Hero -->
  <section class="relative overflow-hidden rounded-3xl border border-border/60 bg-gradient-to-br from-background via-background to-primary/5 p-8 shadow-soft md:p-14">
    <div class="absolute -top-24 -left-10 h-56 w-56 rounded-full bg-primary/10 blur-3xl"></div>
    <div class="absolute -bottom-24 -right-6 h-52 w-52 rounded-full bg-secondary/10 blur-3xl"></div>

    <div class="relative grid gap-10 lg:grid-cols-5 lg:items-center">
      <div class="space-y-8 lg:col-span-3">
        <span class="inline-flex items-center gap-2 rounded-full bg-primary/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.35em] text-primary">
          для команд і викладачів
        </span>
        <div class="space-y-5">
          <h1 class="text-3xl font-bold tracking-tight text-foreground sm:text-4xl xl:text-5xl">
            Центр створення граматичних практик, що поєднує тести, теорію та AI-підказки
          </h1>
          <p class="max-w-2xl text-base leading-relaxed text-muted-foreground sm:text-lg">
            Engram допомагає розробляти й проходити англомовні вправи за хвилини. Готові сценарії, конструктор запитань, автоматичні пояснення та статистика — в одному робочому просторі.
          </p>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
          <a href="{{ route('catalog-tests.cards') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-primary px-6 py-3.5 text-sm font-semibold text-primary-foreground shadow-lg transition hover:translate-y-[-2px] hover:shadow-xl">
            Переглянути каталог тестів
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </a>
          <a href="{{ route('grammar-test') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-border bg-background/80 px-6 py-3.5 text-sm font-semibold text-foreground transition hover:border-primary hover:text-primary">
            Зібрати власний тест
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </a>
        </div>
        @php
          $heroHighlights = [
              ['title' => 'Конструктор із фільтрами', 'description' => 'Швидке збирання тесту за рівнем CEFR, темою, тегами або джерелом.'],
              ['title' => 'Розумні пояснення', 'description' => 'AI-асистент підкаже рівень складності, дасть підказку та пояснить відповідь.'],
              ['title' => 'Командна база знань', 'description' => 'Теоретичні сторінки й рецензії формують єдиний стандарт матеріалів.'],
          ];
        @endphp
        <dl class="grid gap-4 sm:grid-cols-3">
          @foreach($heroHighlights as $highlight)
            <div class="rounded-2xl border border-border/70 bg-card/80 p-4 backdrop-blur-sm">
              <dt class="text-sm font-semibold text-foreground">{{ $highlight['title'] }}</dt>
              <dd class="mt-1 text-sm leading-relaxed text-muted-foreground">{{ $highlight['description'] }}</dd>
            </div>
          @endforeach
        </dl>
      </div>

      <div class="lg:col-span-2">
        <div class="mx-auto max-w-sm space-y-5 rounded-3xl border border-border/70 bg-card p-6 shadow-2xl">
          <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-muted-foreground">динаміка бази</p>
            <h2 class="text-xl font-semibold text-foreground">Ваш прогрес у реальному часі</h2>
          </div>
          <dl class="grid gap-3">
            @php
              $statMeta = [
                  'tests' => ['label' => 'Збережених тестів', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                  'questions' => ['label' => 'Питань у сховищі', 'icon' => 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                  'pages' => ['label' => 'Теоретичних статей', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
                  'tags' => ['label' => 'Тегів для фільтрації', 'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z'],
              ];
            @endphp
            @foreach($statMeta as $key => $meta)
              <div class="flex items-center justify-between rounded-2xl border border-border/70 bg-muted/60 px-4 py-3">
                <div class="flex items-center gap-3">
                  <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $meta['icon'] }}" />
                    </svg>
                  </span>
                  <dt class="text-sm font-medium text-muted-foreground">{{ $meta['label'] }}</dt>
                </div>
                <dd class="text-lg font-semibold text-foreground">{{ number_format($stats[$key] ?? 0, 0, ',', ' ') }}</dd>
              </div>
            @endforeach
          </dl>
          <p class="text-xs text-muted-foreground">
            Показники оновлюються автоматично, коли ви створюєте нові питання або редагуєте існуючі тести.
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- Flow -->
  <section class="grid gap-8 rounded-3xl border border-border/60 bg-card/70 p-8 shadow-soft md:grid-cols-5 md:p-12">
    <div class="space-y-4 md:col-span-2">
      <h2 class="text-2xl font-semibold text-foreground">Від запиту клієнта до готового уроку</h2>
      <p class="text-sm leading-relaxed text-muted-foreground">
        Кожен модуль Engram закриває окремий етап підготовки: збір потреб, генерація вправ, рецензія та підготовка матеріалів до уроку або воркшопу.
      </p>
      <a href="{{ route('saved-tests.list') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-primary transition hover:text-primary/80">
        Переглянути всі збережені тести
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
        </svg>
      </a>
    </div>
    @php
      $flowSteps = [
          ['title' => 'Збір потреб', 'description' => 'Фільтруйте базу за рівнями, тегами й категоріями, щоб визначити обсяг і тип вправ.'],
          ['title' => 'Конструктор тесту', 'description' => 'Додавайте питання вручну або через сидери, налаштовуйте AI-генерацію під конкретне завдання.'],
          ['title' => 'Перевірка та пояснення', 'description' => 'Запитуйте AI про рівень складності, підказки для студентів і правильні відповіді.'],
          ['title' => 'Публікація та повторне використання', 'description' => 'Зберігайте тести, робіть добірки сторінок теорії та позначайте прогрес команди.'],
      ];
    @endphp
    <ol class="space-y-4 md:col-span-3">
      @foreach($flowSteps as $index => $step)
        <li class="flex gap-4 rounded-2xl border border-border/70 bg-background/90 p-5">
          <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary/10 text-sm font-semibold text-primary">{{ $index + 1 }}</span>
          <div class="space-y-1">
            <h3 class="text-sm font-semibold text-foreground">{{ $step['title'] }}</h3>
            <p class="text-sm leading-relaxed text-muted-foreground">{{ $step['description'] }}</p>
          </div>
        </li>
      @endforeach
    </ol>
  </section>

  <!-- Instruments -->
  <section class="space-y-8">
    <div class="space-y-3">
      <h2 class="text-2xl font-semibold text-foreground">Інструменти платформи</h2>
      <p class="max-w-2xl text-sm text-muted-foreground">Комбінуйте готові добірки, власні тестові запитання та теоретичні сторінки, щоб зібрати повну програму навчання.</p>
    </div>
    @php
      $featureCards = [
          [
              'title' => 'Каталог граматичних тестів',
              'description' => 'Добірки запитань за рівнями CEFR, часами та темами. Фільтруйте за тегами й отримуйте сценарій уроку за хвилини.',
              'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
              'link' => route('catalog-tests.cards'),
              'cta' => 'Відкрити каталог',
          ],
          [
              'title' => 'Конструктор тестів',
              'description' => 'Створюйте власні тести: додавайте запитання, керуйте рівнями, зберігайте добірки та експортуйте їх для занять.',
              'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
              'link' => route('grammar-test'),
              'cta' => 'Зібрати тест',
          ],
          [
              'title' => 'Теоретичні сторінки',
              'description' => 'Короткі пояснення граматики українською мовою з прикладами й таблицями. Ідеально під час живого заняття.',
              'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
              'link' => route('pages.index'),
              'cta' => 'Перейти до теорії',
          ],
          [
              'title' => 'Рецензії та пояснення запитань',
              'description' => 'Залучайте експертів для перевірки запитань, зберігайте коментарі та використовуйте їх як стандарт для команди.',
              'icon' => 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z',
              'link' => route('question-review.index'),
              'cta' => 'Перейти до рецензій',
          ],
      ];
    @endphp
    <div class="grid gap-6 md:grid-cols-2">
      @foreach($featureCards as $feature)
        <article class="group flex h-full flex-col justify-between rounded-3xl border border-border/70 bg-card p-7 shadow-soft transition hover:-translate-y-2 hover:border-primary/60 hover:shadow-xl">
          <div class="space-y-4">
            <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-primary/10 text-primary">
              <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $feature['icon'] }}" />
              </svg>
            </span>
            <h3 class="text-lg font-semibold text-foreground">{{ $feature['title'] }}</h3>
            <p class="text-sm leading-relaxed text-muted-foreground">{{ $feature['description'] }}</p>
          </div>
          <a href="{{ $feature['link'] }}" class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-primary transition group-hover:gap-3">
            {{ $feature['cta'] }}
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </a>
        </article>
      @endforeach
    </div>
  </section>

  <!-- Latest Tests -->
  @if($latestTests->isNotEmpty())
    <section class="space-y-8">
      <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div class="space-y-1">
          <h2 class="text-2xl font-semibold text-foreground">Нещодавно збережені тести</h2>
          <p class="text-sm text-muted-foreground">Свіжі добірки для уроків і тренінгів із зазначеними рівнями та кількістю завдань.</p>
        </div>
        <a href="{{ route('catalog-tests.cards') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-primary transition hover:text-primary/80">
          Переглянути весь каталог
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
          </svg>
        </a>
      </div>
      <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($latestTests as $test)
          <article class="group flex h-full flex-col rounded-3xl border border-border/70 bg-card p-6 shadow-soft transition hover:-translate-y-2 hover:shadow-xl">
            <div class="space-y-3">
              <div class="flex items-center gap-2 text-xs text-muted-foreground">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Оновлено {{ optional($test->updated_at)->format('d.m.Y') }}
              </div>
              <h3 class="text-lg font-semibold text-foreground">{{ $test->name }}</h3>
              @if($test->description)
                <p class="text-sm leading-relaxed text-muted-foreground">{{ \Illuminate\Support\Str::limit(strip_tags($test->description), 140) }}</p>
              @endif
            </div>
            <dl class="mt-4 space-y-2 text-sm text-muted-foreground">
              <div class="flex items-center justify-between rounded-xl bg-muted/60 px-3 py-2">
                <dt class="flex items-center gap-2">
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                  </svg>
                  Завдань
                </dt>
                <dd class="font-semibold text-foreground">{{ $test->question_links_count }}</dd>
              </div>
              @php
                $levels = collect($test->filters['levels'] ?? $test->filters['level'] ?? [])->filter()->unique()->values();
              @endphp
              @if($levels->isNotEmpty())
                <div class="flex items-center justify-between rounded-xl bg-muted/60 px-3 py-2">
                  <dt class="flex items-center gap-2">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    Рівні
                  </dt>
                  <dd class="font-semibold text-foreground">{{ $levels->implode(', ') }}</dd>
                </div>
              @endif
            </dl>
            <a href="{{ route('saved-test.js', $test->slug) }}" class="mt-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-secondary px-4 py-2.5 text-sm font-semibold text-secondary-foreground shadow-sm transition hover:translate-y-[-2px] hover:shadow-md">
              Перейти до тесту
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
              </svg>
            </a>
          </article>
        @endforeach
      </div>
    </section>
  @endif

  <!-- Knowledge base -->
  @if($featuredCategories->isNotEmpty() || $recentPages->isNotEmpty())
    <section class="space-y-8">
      <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div class="space-y-1">
          <h2 class="text-2xl font-semibold text-foreground">База знань і теорія</h2>
          <p class="text-sm text-muted-foreground">Перейдіть до структурованих пояснень і сторінок, які доповнюють граматичні вправи.</p>
        </div>
        <a href="{{ route('pages.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-primary transition hover:text-primary/80">
          Відкрити всі сторінки
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
          </svg>
        </a>
      </div>
      @if($featuredCategories->isNotEmpty())
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
          @foreach($featuredCategories as $category)
            <article class="group rounded-3xl border border-border/70 bg-card p-6 shadow-soft transition hover:-translate-y-2 hover:border-accent/60 hover:shadow-xl">
              <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-2xl bg-accent/10 text-accent">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                </svg>
              </div>
              <h3 class="text-base font-semibold text-foreground">{{ $category->title }}</h3>
              <p class="mt-2 text-sm text-muted-foreground">{{ $category->pages_count }} матеріал(и/ів)</p>
              <a href="{{ route('pages.category', $category->slug) }}" class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-accent transition group-hover:gap-3">
                Переглянути
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
              </a>
            </article>
          @endforeach
        </div>
      @endif
      @if($recentPages->isNotEmpty())
        <div class="grid gap-6 md:grid-cols-2">
          @foreach($recentPages as $page)
            <article class="group flex h-full flex-col justify-between rounded-3xl border border-border/70 bg-card p-6 shadow-soft transition hover:-translate-y-2 hover:shadow-xl">
              <div class="space-y-3">
                <span class="inline-flex items-center gap-2 rounded-full bg-muted px-3 py-1 text-xs font-semibold text-muted-foreground">
                  <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                  </svg>
                  {{ $page->category->title }}
                </span>
                <h3 class="text-lg font-semibold text-foreground">{{ $page->title }}</h3>
                <p class="text-sm leading-relaxed text-muted-foreground">{{ \Illuminate\Support\Str::limit(strip_tags($page->text), 150) }}</p>
              </div>
              <a href="{{ route('pages.show', [$page->category->slug, $page->slug]) }}" class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-primary transition group-hover:gap-3">
                Читати далі
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
              </a>
            </article>
          @endforeach
        </div>
      @endif
    </section>
  @endif

  <!-- CTA -->
  <section class="relative overflow-hidden rounded-3xl border border-border/70 bg-gradient-to-br from-secondary/15 via-background to-primary/10 p-10 text-center shadow-xl md:p-14">
    <div class="absolute -left-20 bottom-0 h-48 w-48 rounded-full bg-secondary/20 blur-3xl"></div>
    <div class="absolute -right-16 top-0 h-48 w-48 rounded-full bg-primary/20 blur-3xl"></div>
    <div class="relative mx-auto max-w-2xl space-y-6">
      <h2 class="text-3xl font-bold text-foreground md:text-4xl">Запросіть демо Engram для своєї команди</h2>
      <p class="text-base leading-relaxed text-muted-foreground">Ми проведемо презентацію, покажемо налаштування конструктору та надамо рекомендації щодо запуску у вашому навчальному процесі.</p>
      <div class="flex flex-col items-center justify-center gap-4 sm:flex-row">
        <a href="mailto:hello@engram.app" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-primary px-8 py-3.5 text-sm font-semibold text-primary-foreground shadow-lg transition hover:translate-y-[-2px] hover:shadow-xl">
          Запросити демо
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
          </svg>
        </a>
        <a href="https://t.me/engram_app" class="inline-flex items-center gap-2 text-sm font-semibold text-primary transition hover:text-primary/80">
          Написати в Telegram
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
          </svg>
        </a>
      </div>
    </div>
  </section>
</div>
@endsection
