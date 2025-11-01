@extends('layouts.engram')

@section('title', 'Engram — Платформа для вивчення англійської')

@section('content')
  @php
    $primaryActions = [
      [
        'label' => 'Каталог граматичних тестів',
        'href' => route('catalog-tests.cards'),
        'description' => 'Конструктор і готові добірки вправ',
      ],
      [
        'label' => 'База теорії',
        'href' => route('pages.index'),
        'description' => 'Пояснення, таблиці, приклади вживання',
      ],
    ];

    $featureCards = [
      [
        'icon' => '🧠',
        'title' => 'Розумні тести',
        'text' => 'Підбір запитань за тегами, режим карток та покрокові перевірки, щоб краще запам’ятовувати правила.',
        'href' => route('catalog-tests.cards'),
        'linkLabel' => 'Перейти до тестів',
      ],
      [
        'icon' => '📘',
        'title' => 'Лаконічна теорія',
        'text' => 'Структуровані сторінки з граматики англійської: часові форми, конструкції, винятки та поради.',
        'href' => route('pages.index'),
        'linkLabel' => 'Переглянути матеріали',
      ],
      [
        'icon' => '🤖',
        'title' => 'AI-допомога',
        'text' => 'Спробуйте адаптивний AI-тест, який підлаштовується до рівня, пропонує підказки та пояснення.',
        'href' => route('ai-test.form'),
        'linkLabel' => 'Запустити AI-тест',
      ],
    ];

    $journey = [
      [
        'title' => '1. Оберіть мету',
        'text' => 'Швидке повторення часів, підготовка до співбесіди або щоденні 10-хвилинні вправи? Вкажіть, що саме хочете прокачати.',
      ],
      [
        'title' => '2. Виконуйте практику',
        'text' => 'Проходьте добірки, зберігайте улюблені тести, слідкуйте за прогресом і відзначайте складні запитання.',
      ],
      [
        'title' => '3. Закріплюйте з теорією',
        'text' => 'Переходьте до коротких шпаргалок, перечитуйте пояснення й одразу перевіряйте себе на завданнях.',
      ],
    ];

    $pillars = [
      [
        'title' => 'Граматика без води',
        'text' => 'Тільки те, що допомагає говорити та писати впевненіше. Кожен матеріал супроводжується прикладами українською.',
      ],
      [
        'title' => 'Інтерактивні сценарії',
        'text' => 'Карточки, покрокові завдання, drag&drop і формати з відкритою відповіддю. Нудьгувати не доведеться.',
      ],
      [
        'title' => 'Просте відстеження прогресу',
        'text' => 'Зберігайте сесії, фіксуйте помилки й повертайтеся до складних вправ у будь-який момент.',
      ],
    ];
  @endphp

  <div class="space-y-20">
    <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-primary/10 via-secondary/10 to-accent/10 border border-border/60 px-6 py-12 sm:px-12 lg:px-16">
      <div class="grid gap-12 lg:grid-cols-[1.2fr_minmax(0,1fr)] lg:items-center">
        <div class="space-y-6">
          <span class="inline-flex items-center rounded-full bg-primary/10 px-4 py-1 text-sm font-semibold text-primary">Публічний прев’ю</span>
          <h1 class="text-3xl sm:text-4xl lg:text-5xl font-semibold tracking-tight leading-tight">Єдина точка для вправ, теорії та AI-підказок з англійської</h1>
          <p class="text-base sm:text-lg text-muted-foreground max-w-xl">Engram допомагає системно тренувати граматику: від швидких перевірок до глибоких розборів. Жодних зайвих переходів — усе під рукою.</p>
          <div class="flex flex-wrap gap-3">
            @foreach ($primaryActions as $action)
              <a href="{{ $action['href'] }}" class="group flex flex-col rounded-2xl border border-border bg-card px-5 py-4 shadow-soft transition hover:border-primary/60 hover:shadow-lg">
                <span class="text-sm font-semibold text-foreground">{{ $action['label'] }}</span>
                <span class="text-xs text-muted-foreground mt-1">{{ $action['description'] }}</span>
              </a>
            @endforeach
          </div>
          <div class="flex flex-wrap items-center gap-x-6 gap-y-3 text-sm text-muted-foreground">
            <div class="flex items-center gap-2">
              <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-secondary/15 text-secondary">★</span>
              <span>Режими «Картки», «Покроково» та «Drag & Drop»</span>
            </div>
            <div class="flex items-center gap-2">
              <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-accent/15 text-accent">⚡</span>
              <span>Миттєвий пошук тем і прикладів</span>
            </div>
          </div>
        </div>
        <div class="relative">
          <div class="absolute -top-10 -left-10 h-36 w-36 rounded-full bg-primary/20 blur-3xl"></div>
          <div class="absolute -bottom-12 -right-8 h-32 w-32 rounded-full bg-secondary/20 blur-3xl"></div>
          <div class="relative rounded-3xl border border-border/60 bg-background/80 p-6 shadow-soft backdrop-blur">
            <h2 class="text-lg font-semibold">Що всередині:</h2>
            <ul class="mt-4 space-y-3 text-sm text-muted-foreground">
              <li class="flex items-start gap-3"><span class="mt-1 text-lg">✅</span><span>Готові й кастомні добірки завдань з поясненнями.</span></li>
              <li class="flex items-start gap-3"><span class="mt-1 text-lg">✅</span><span>Підказки та нотатки українською для швидкого розуміння.</span></li>
              <li class="flex items-start gap-3"><span class="mt-1 text-lg">✅</span><span>Збереження прогресу та повторення у власному темпі.</span></li>
            </ul>
            <div class="mt-6 rounded-2xl bg-muted px-4 py-3 text-xs text-muted-foreground">
              Порада: додайте у закладки сторінку каталогу тестів — там можна комбінувати теги, складність і типи завдань.
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="space-y-6">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <h2 class="text-2xl font-semibold">Можливості платформи</h2>
          <p class="text-sm text-muted-foreground">Поєднуйте практику, пояснення та AI-інструменти, щоб отримати максимум від кожної сесії.</p>
        </div>
        <a href="{{ route('site.search') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-primary hover:text-primary/80">
          Перейти до пошуку
          <span aria-hidden="true">→</span>
        </a>
      </div>
      <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($featureCards as $feature)
          <article class="flex h-full flex-col justify-between rounded-3xl border border-border bg-card/80 p-6 shadow-soft transition hover:-translate-y-1 hover:border-primary/60 hover:shadow-xl">
            <div class="space-y-4">
              <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-muted text-2xl">{{ $feature['icon'] }}</span>
              <div class="space-y-2">
                <h3 class="text-xl font-semibold">{{ $feature['title'] }}</h3>
                <p class="text-sm text-muted-foreground">{{ $feature['text'] }}</p>
              </div>
            </div>
            <div class="pt-6">
              <a href="{{ $feature['href'] }}" class="inline-flex items-center gap-2 text-sm font-semibold text-primary hover:text-primary/80">
                {{ $feature['linkLabel'] }}
                <span aria-hidden="true">→</span>
              </a>
            </div>
          </article>
        @endforeach
      </div>
    </section>

    <section class="space-y-8">
      <div class="grid gap-6 lg:grid-cols-[1fr_1.1fr] lg:items-center">
        <div class="space-y-4">
          <h2 class="text-2xl font-semibold">Побудуйте власний маршрут навчання</h2>
          <p class="text-sm text-muted-foreground">Ми підготували базовий сценарій, який допомагає планувати заняття навіть без викладача.</p>
          <div class="space-y-4">
            @foreach ($journey as $step)
              <div class="rounded-3xl border border-border bg-card px-5 py-4 shadow-sm">
                <h3 class="text-base font-semibold">{{ $step['title'] }}</h3>
                <p class="mt-1 text-sm text-muted-foreground">{{ $step['text'] }}</p>
              </div>
            @endforeach
          </div>
        </div>
        <div class="rounded-3xl border border-dashed border-primary/40 bg-primary/5 p-6 shadow-soft">
          <h3 class="text-lg font-semibold">Швидкі посилання</h3>
          <ul class="mt-4 space-y-3 text-sm text-muted-foreground">
            <li class="flex items-center justify-between gap-3 rounded-2xl bg-background px-4 py-3 shadow-sm">
              <div>
                <p class="font-medium text-foreground">Збережені тести</p>
                <p class="text-xs text-muted-foreground">Поверніться до вправ, які вже пройшли, і відстежуйте помилки.</p>
              </div>
              <a href="{{ route('saved-tests.cards') }}" class="shrink-0 rounded-full bg-primary px-4 py-2 text-xs font-semibold text-primary-foreground hover:bg-primary/90">Відкрити</a>
            </li>
            <li class="flex items-center justify-between gap-3 rounded-2xl bg-background px-4 py-3 shadow-sm">
              <div>
                <p class="font-medium text-foreground">AI-конструктор тесту</p>
                <p class="text-xs text-muted-foreground">Дайте кілька підказок — система сама згенерує вправи.</p>
              </div>
              <a href="{{ route('ai-test.form') }}" class="shrink-0 rounded-full border border-primary px-4 py-2 text-xs font-semibold text-primary hover:bg-primary/10">Спробувати</a>
            </li>
            <li class="flex items-center justify-between gap-3 rounded-2xl bg-background px-4 py-3 shadow-sm">
              <div>
                <p class="font-medium text-foreground">База тем</p>
                <p class="text-xs text-muted-foreground">Перелік усіх доступних сторінок із поясненнями та прикладами.</p>
              </div>
              <a href="{{ route('pages.index') }}" class="shrink-0 rounded-full border border-secondary px-4 py-2 text-xs font-semibold text-secondary hover:bg-secondary/10">Переглянути</a>
            </li>
          </ul>
        </div>
      </div>
    </section>

    <section class="rounded-3xl border border-border bg-card px-6 py-10 shadow-soft">
      <div class="grid gap-8 md:grid-cols-3">
        @foreach ($pillars as $pillar)
          <div class="flex flex-col gap-3">
            <div class="h-1 w-12 rounded-full bg-primary"></div>
            <h3 class="text-lg font-semibold">{{ $pillar['title'] }}</h3>
            <p class="text-sm text-muted-foreground">{{ $pillar['text'] }}</p>
          </div>
        @endforeach
      </div>
      <div class="mt-10 flex flex-col gap-4 rounded-3xl bg-muted/80 px-6 py-6 text-sm text-muted-foreground md:flex-row md:items-center md:justify-between">
        <div>
          <p class="text-base font-semibold text-foreground">Потрібна допомога чи маєте ідею?</p>
          <p>Напишіть нам — розширюємо публічний функціонал за фідбеком спільноти.</p>
        </div>
        <a href="#faq" class="inline-flex items-center gap-2 rounded-full bg-foreground px-5 py-2 text-sm font-semibold text-background hover:bg-foreground/90">
          Залишити відгук
          <span aria-hidden="true">→</span>
        </a>
      </div>
    </section>
  </div>
@endsection
