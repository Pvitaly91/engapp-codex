@extends('layouts.engram')

@section('title', 'Gramlyze — платформа для викладачів англійської')

@section('content')
<div class="space-y-16">
  <!-- HERO WITH VIDEO PLACEHOLDER -->
  <section id="hero" data-animate class="relative">
    <div class="grid gap-8 lg:grid-cols-2 lg:gap-12 items-center">
      <div class="space-y-8" data-animate data-animate-delay="100">
        <div class="inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/5 px-4 py-2 text-xs font-semibold text-primary">
          <span class="relative flex h-2 w-2">
            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-primary opacity-75"></span>
            <span class="relative inline-flex h-2 w-2 rounded-full bg-primary"></span>
          </span>
          Новий реліз: версія 2.0 вже доступна
        </div>
        
        <h1 class="text-4xl font-bold tracking-tight text-foreground lg:text-6xl">
          Усе для уроків англійської в <span class="bg-gradient-to-r from-primary via-secondary to-accent bg-clip-text text-transparent">одному місці</span>
        </h1>
        
        <p class="text-lg leading-relaxed text-muted-foreground">
          Створюйте тести, керуйте навчальними матеріалами та відстежуйте прогрес студентів за допомогою AI. Gramlyze — це комплексна платформа для сучасних викладачів англійської мови.
        </p>
        
        <div class="flex flex-wrap gap-4">
          <a href="{{ route('catalog-tests.cards') }}" class="group inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-8 py-4 text-base font-semibold text-primary-foreground shadow-lg transition-all hover:scale-105 hover:shadow-xl">
            Почати безкоштовно
            <svg class="h-5 w-5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </a>
          <a href="#features" class="inline-flex items-center justify-center gap-2 rounded-xl border-2 border-border px-8 py-4 text-base font-semibold text-foreground transition-all hover:border-primary hover:bg-primary/5">
            Дізнатися більше
          </a>
        </div>

        <div class="flex flex-wrap items-center gap-6 pt-4 text-sm text-muted-foreground">
          <div class="flex items-center gap-2">
            <svg class="h-5 w-5 text-primary" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span>Безкоштовний старт</span>
          </div>
          <div class="flex items-center gap-2">
            <svg class="h-5 w-5 text-primary" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span>AI-асистент</span>
          </div>
          <div class="flex items-center gap-2">
            <svg class="h-5 w-5 text-primary" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span>Підтримка команд</span>
          </div>
        </div>
      </div>

      <div class="relative" data-animate data-animate-delay="200">
        <div class="absolute -inset-4 rounded-3xl bg-gradient-to-r from-primary/20 via-secondary/20 to-accent/20 blur-2xl"></div>
        <div class="relative overflow-hidden rounded-2xl border border-border/50 bg-card shadow-2xl">
          <div class="aspect-video bg-gradient-to-br from-primary/10 via-secondary/10 to-accent/10 flex items-center justify-center">
            <div class="text-center space-y-4 p-8">
              <div class="inline-flex h-20 w-20 items-center justify-center rounded-full bg-primary/10 text-primary">
                <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <p class="text-sm font-medium text-muted-foreground">Демо-відео платформи</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- FEATURES GRID -->
  <section id="features" class="space-y-12" data-animate>
    <div class="text-center space-y-4" data-animate data-animate-delay="100">
      <h2 class="text-3xl font-bold tracking-tight text-foreground lg:text-4xl">
        Потужні інструменти для ефективного викладання
      </h2>
      <p class="mx-auto max-w-2xl text-lg text-muted-foreground">
        Gramlyze надає все необхідне для створення, управління та аналізу навчальних матеріалів
      </p>
    </div>

    @php
      $features = [
        [
          'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
          'title' => 'Каталог готових тестів',
          'description' => 'Доступ до великої бібліотеки готових тестів з різних тем та рівнів складності. Фільтруйте за CEFR, темами та типами завдань.',
          'link' => route('catalog-tests.cards'),
          'linkText' => 'Відкрити каталог'
        ],
        [
          'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
          'title' => 'Конструктор тестів',
          'description' => 'Створюйте власні тести з нуля або на основі існуючих. Налаштовуйте типи питань, складність та додавайте мультимедіа.',
          'link' => route('grammar-test'),
          'linkText' => 'Створити тест'
        ],
        [
          'icon' => 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z',
          'title' => 'AI-асистент',
          'description' => 'Штучний інтелект допомагає перевіряти відповіді, генерувати пояснення та рекомендації для студентів у режимі реального часу.',
          'link' => route('ai-test.form'),
          'linkText' => 'Спробувати AI'
        ],
        [
          'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
          'title' => 'База теоретичних знань',
          'description' => 'Структурована база знань з граматики, лексики та вимови. Швидкий доступ до правил та прикладів для підготовки уроків.',
          'link' => route('pages.index'),
          'linkText' => 'Відкрити теорію'
        ],
        [
          'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
          'title' => 'Аналітика та звіти',
          'description' => 'Відстежуйте прогрес студентів, аналізуйте результати тестів та отримуйте детальні звіти для покращення навчального процесу.',
          'link' => route('question-review.index'),
          'linkText' => 'Переглянути рецензії'
        ],
        [
          'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
          'title' => 'Командна робота',
          'description' => 'Співпрацюйте з колегами, діліться матеріалами та координуйте навчальний процес в одній платформі.',
          'link' => '#team',
          'linkText' => 'Дізнатися більше'
        ],
      ];
    @endphp

    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3" data-animate data-animate-delay="200">
      @foreach ($features as $feature)
        <div class="group relative overflow-hidden rounded-2xl border border-border bg-card p-6 transition-all hover:border-primary/50 hover:shadow-lg">
          <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 text-primary transition-transform group-hover:scale-110">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $feature['icon'] }}" />
            </svg>
          </div>
          <h3 class="mb-2 text-xl font-semibold text-foreground">{{ $feature['title'] }}</h3>
          <p class="mb-4 text-sm text-muted-foreground">{{ $feature['description'] }}</p>
          <a href="{{ $feature['link'] }}" class="inline-flex items-center gap-2 text-sm font-semibold text-primary transition-all hover:gap-3">
            {{ $feature['linkText'] }}
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </a>
        </div>
      @endforeach
    </div>
  </section>

  <!-- STATS -->
  <section id="stats" class="overflow-hidden rounded-3xl border border-border bg-gradient-to-br from-primary/5 via-secondary/5 to-accent/5 p-8 lg:p-12" data-animate>
    <div class="grid gap-8 lg:grid-cols-2 lg:gap-12 items-center">
      <div class="space-y-6" data-animate data-animate-delay="100">
        <div class="inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-4 py-1.5 text-xs font-semibold text-primary">
          <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd" />
          </svg>
          Постійно зростаюча база
        </div>
        <h2 class="text-3xl font-bold tracking-tight text-foreground lg:text-4xl">
          Платформа, що розвивається разом з вами
        </h2>
        <p class="text-lg text-muted-foreground">
          Щодня викладачі додають нові матеріали, створюють тести та діляться досвідом. Приєднуйтесь до спільноти професіоналів.
        </p>
        <a href="{{ route('saved-tests.list') }}" class="inline-flex items-center gap-2 text-base font-semibold text-primary transition-all hover:gap-3">
          Переглянути всі тести
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
          </svg>
        </a>
      </div>

      <div class="grid grid-cols-2 gap-4" data-animate data-animate-delay="200">
        @php
          $statsList = [
            ['value' => $stats['tests'] ?? 0, 'label' => 'Готових тестів', 'color' => 'primary'],
            ['value' => $stats['questions'] ?? 0, 'label' => 'Питань у базі', 'color' => 'secondary'],
            ['value' => $stats['pages'] ?? 0, 'label' => 'Сторінок теорії', 'color' => 'accent'],
            ['value' => $stats['tags'] ?? 0, 'label' => 'Тегів', 'color' => 'primary'],
          ];
        @endphp
        @foreach ($statsList as $stat)
          <div class="rounded-2xl border border-border bg-card p-6 text-center transition-all hover:scale-105 hover:shadow-lg">
            <div class="text-4xl font-bold lg:text-5xl {{ $stat['color'] === 'primary' ? 'text-primary' : ($stat['color'] === 'secondary' ? 'text-secondary' : 'text-accent') }}">{{ number_format($stat['value'], 0, ',', ' ') }}</div>
            <div class="mt-2 text-sm font-medium text-muted-foreground">{{ $stat['label'] }}</div>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  <!-- HOW IT WORKS -->
  <section id="how-it-works" class="space-y-12" data-animate>
    <div class="text-center space-y-4" data-animate data-animate-delay="100">
      <h2 class="text-3xl font-bold tracking-tight text-foreground lg:text-4xl">
        Як це працює
      </h2>
      <p class="mx-auto max-w-2xl text-lg text-muted-foreground">
        Простий процес від ідеї до готового уроку
      </p>
    </div>

    @php
      $steps = [
        [
          'number' => '01',
          'title' => 'Оберіть або створіть',
          'description' => 'Виберіть готовий тест з каталогу або створіть власний з нуля. Використовуйте фільтри за рівнем, темою та типом завдань.',
          'icon' => 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z'
        ],
        [
          'number' => '02',
          'title' => 'Налаштуйте під потреби',
          'description' => 'Додайте власні питання, змініть складність, налаштуйте AI-підказки та пояснення для студентів.',
          'icon' => 'M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4'
        ],
        [
          'number' => '03',
          'title' => 'Проведіть урок',
          'description' => 'Діліться тестом зі студентами через посилання або експортуйте в PDF. Відстежуйте виконання в реальному часі.',
          'icon' => 'M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122'
        ],
        [
          'number' => '04',
          'title' => 'Аналізуйте результати',
          'description' => 'Переглядайте детальну аналітику, виявляйте слабкі місця студентів та покращуйте наступні уроки на основі даних.',
          'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'
        ],
      ];
    @endphp

    <div class="relative" data-animate data-animate-delay="200">
      <!-- Connection line for desktop -->
      <div class="absolute left-8 top-16 hidden h-[calc(100%-8rem)] w-0.5 bg-gradient-to-b from-primary via-secondary to-accent lg:block"></div>
      
      <div class="space-y-8 lg:space-y-12">
        @foreach ($steps as $index => $step)
          <div class="relative grid gap-6 lg:grid-cols-[auto_1fr] lg:gap-8">
            <div class="relative flex items-start lg:items-center">
              <div class="flex h-16 w-16 items-center justify-center rounded-2xl border-4 border-background bg-primary text-2xl font-bold text-white shadow-lg">
                {{ $step['number'] }}
              </div>
            </div>
            <div class="rounded-2xl border border-border bg-card p-6 transition-all hover:border-primary/50 hover:shadow-lg lg:p-8">
              <div class="flex items-start gap-4">
                <div class="hidden lg:block">
                  <div class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 text-primary">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $step['icon'] }}" />
                    </svg>
                  </div>
                </div>
                <div class="flex-1 space-y-2">
                  <h3 class="text-xl font-semibold text-foreground lg:text-2xl">{{ $step['title'] }}</h3>
                  <p class="text-muted-foreground">{{ $step['description'] }}</p>
                </div>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  <!-- CTA SECTION -->
  <section id="team" class="overflow-hidden rounded-3xl border border-border bg-gradient-to-br from-primary to-secondary p-8 text-primary-foreground lg:p-16" data-animate>
    <div class="grid gap-8 lg:grid-cols-2 lg:gap-12 items-center">
      <div class="space-y-6" data-animate data-animate-delay="100">
        <h2 class="text-3xl font-bold tracking-tight lg:text-5xl">
          Готові розпочати безкоштовно?
        </h2>
        <p class="text-lg text-primary-foreground/90">
          Приєднуйтесь до Gramlyze сьогодні та отримайте доступ до всіх функцій платформи. Без прихованих платежів, без обмежень.
        </p>
        
        <div class="space-y-4">
          <div class="flex items-center gap-3">
            <svg class="h-6 w-6 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span>Безкоштовний доступ до всіх функцій</span>
          </div>
          <div class="flex items-center gap-3">
            <svg class="h-6 w-6 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span>AI-асистент для перевірки та пояснень</span>
          </div>
          <div class="flex items-center gap-3">
            <svg class="h-6 w-6 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span>Підтримка командної роботи</span>
          </div>
          <div class="flex items-center gap-3">
            <svg class="h-6 w-6 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span>Регулярні оновлення та нові функції</span>
          </div>
        </div>

        <div class="flex flex-wrap gap-4 pt-4">
          <a href="{{ route('catalog-tests.cards') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-background px-8 py-4 text-base font-semibold text-foreground transition-all hover:scale-105 hover:shadow-xl">
            Почати зараз
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </a>
          <a href="mailto:hello@gramlyze.com" class="inline-flex items-center justify-center gap-2 rounded-xl border-2 border-primary-foreground/40 px-8 py-4 text-base font-semibold text-primary-foreground transition-all hover:bg-primary-foreground/10">
            Зв'язатися з нами
          </a>
        </div>
      </div>

      <div class="relative" data-animate data-animate-delay="200">
        <div class="rounded-2xl border border-primary-foreground/20 bg-primary-foreground/10 p-8 backdrop-blur">
          <div class="space-y-6">
            <div class="space-y-2">
              <div class="text-sm font-semibold uppercase tracking-wider text-primary-foreground/80">Що включено</div>
              <div class="text-3xl font-bold">Все, що вам потрібно</div>
            </div>
            
            <div class="space-y-4">
              @php
                $included = [
                  'Необмежена кількість тестів',
                  'Доступ до каталогу матеріалів',
                  'AI-інтеграція',
                  'Теоретична база знань',
                  'Аналітика та звіти',
                  'Командна співпраця',
                  'Технічна підтримка'
                ];
              @endphp
              @foreach ($included as $item)
                <div class="flex items-center gap-3 text-primary-foreground/90">
                  <svg class="h-5 w-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                  </svg>
                  <span>{{ $item }}</span>
                </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>
@endsection
