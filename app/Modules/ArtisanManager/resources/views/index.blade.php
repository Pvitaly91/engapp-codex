@extends('layouts.app')

@section('title', 'Менеджер Artisan команд')

@section('content')
  <div class="max-w-4xl mx-auto space-y-8">
    <header class="space-y-2 text-center">
      <h1 class="text-3xl font-semibold">Керування Artisan командами</h1>
      <p class="text-muted-foreground">Запускайте команди Laravel Artisan безпосередньо з адмін-панелі.</p>
    </header>

    @if($feedback)
      <div @class([
        'rounded-2xl border p-4 shadow-soft',
        'border-success/40 bg-success/10 text-success' => $feedback['status'] === 'success',
        'border-destructive/40 bg-destructive/10 text-destructive-foreground' => $feedback['status'] === 'error',
      ])>
        <div class="font-medium">{{ $feedback['message'] }}</div>
        @if(! empty($feedback['output']))
          <pre class="mt-3 max-h-64 overflow-y-auto whitespace-pre-wrap rounded-xl border border-border/70 bg-background/70 p-3 text-xs leading-relaxed">{{ $feedback['output'] }}</pre>
        @endif
      </div>
    @endif

    <section class="rounded-3xl border border-border/70 bg-card shadow-soft">
      <div class="space-y-6 p-6">
        <div>
          <h2 class="text-2xl font-semibold">Очищення кешів</h2>
          <p class="text-sm text-muted-foreground">Команди для очищення різних типів кешів в додатку.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <form method="POST" action="{{ route('artisan.cache.clear') }}">
            @csrf
            <button type="submit" class="w-full inline-flex items-center justify-center rounded-2xl bg-primary px-5 py-3 text-sm font-semibold text-primary-foreground shadow-soft hover:bg-primary/90">
              <i class="fa-solid fa-broom mr-2"></i>
              Очистити кеш застосунку
            </button>
          </form>

          <form method="POST" action="{{ route('artisan.config.clear') }}">
            @csrf
            <button type="submit" class="w-full inline-flex items-center justify-center rounded-2xl bg-primary px-5 py-3 text-sm font-semibold text-primary-foreground shadow-soft hover:bg-primary/90">
              <i class="fa-solid fa-cog mr-2"></i>
              Очистити кеш конфігурації
            </button>
          </form>

          <form method="POST" action="{{ route('artisan.route.clear') }}">
            @csrf
            <button type="submit" class="w-full inline-flex items-center justify-center rounded-2xl bg-primary px-5 py-3 text-sm font-semibold text-primary-foreground shadow-soft hover:bg-primary/90">
              <i class="fa-solid fa-route mr-2"></i>
              Очистити кеш маршрутів
            </button>
          </form>

          <form method="POST" action="{{ route('artisan.view.clear') }}">
            @csrf
            <button type="submit" class="w-full inline-flex items-center justify-center rounded-2xl bg-primary px-5 py-3 text-sm font-semibold text-primary-foreground shadow-soft hover:bg-primary/90">
              <i class="fa-solid fa-eye mr-2"></i>
              Очистити скомпільовані шаблони
            </button>
          </form>

          <form method="POST" action="{{ route('artisan.event.clear') }}">
            @csrf
            <button type="submit" class="w-full inline-flex items-center justify-center rounded-2xl bg-primary px-5 py-3 text-sm font-semibold text-primary-foreground shadow-soft hover:bg-primary/90">
              <i class="fa-solid fa-calendar-check mr-2"></i>
              Очистити кеш подій
            </button>
          </form>

          <form method="POST" action="{{ route('artisan.optimize.clear') }}">
            @csrf
            <button type="submit" class="w-full inline-flex items-center justify-center rounded-2xl bg-warning px-5 py-3 text-sm font-semibold text-foreground shadow-soft hover:bg-warning/90">
              <i class="fa-solid fa-trash-can mr-2"></i>
              Очистити всі кеші
            </button>
          </form>
        </div>
      </div>
    </section>

    <section class="rounded-3xl border border-border/70 bg-card shadow-soft">
      <div class="space-y-6 p-6">
        <div>
          <h2 class="text-2xl font-semibold">Оптимізація та кешування</h2>
          <p class="text-sm text-muted-foreground">Команди для підвищення продуктивності застосунку.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <form method="POST" action="{{ route('artisan.optimize') }}">
            @csrf
            <button type="submit" class="w-full inline-flex items-center justify-center rounded-2xl bg-success px-5 py-3 text-sm font-semibold text-primary-foreground shadow-soft hover:bg-success/90">
              <i class="fa-solid fa-bolt mr-2"></i>
              Оптимізувати застосунок
            </button>
          </form>

          <form method="POST" action="{{ route('artisan.config.cache') }}">
            @csrf
            <button type="submit" class="w-full inline-flex items-center justify-center rounded-2xl bg-secondary px-5 py-3 text-sm font-semibold text-secondary-foreground shadow-soft hover:bg-secondary/90">
              <i class="fa-solid fa-cog mr-2"></i>
              Кешувати конфігурацію
            </button>
          </form>

          <form method="POST" action="{{ route('artisan.route.cache') }}">
            @csrf
            <button type="submit" class="w-full inline-flex items-center justify-center rounded-2xl bg-secondary px-5 py-3 text-sm font-semibold text-secondary-foreground shadow-soft hover:bg-secondary/90">
              <i class="fa-solid fa-route mr-2"></i>
              Кешувати маршрути
            </button>
          </form>

          <form method="POST" action="{{ route('artisan.view.cache') }}">
            @csrf
            <button type="submit" class="w-full inline-flex items-center justify-center rounded-2xl bg-secondary px-5 py-3 text-sm font-semibold text-secondary-foreground shadow-soft hover:bg-secondary/90">
              <i class="fa-solid fa-eye mr-2"></i>
              Прекомпілювати шаблони
            </button>
          </form>

          <form method="POST" action="{{ route('artisan.event.cache') }}">
            @csrf
            <button type="submit" class="w-full inline-flex items-center justify-center rounded-2xl bg-secondary px-5 py-3 text-sm font-semibold text-secondary-foreground shadow-soft hover:bg-secondary/90">
              <i class="fa-solid fa-calendar-check mr-2"></i>
              Кешувати події
            </button>
          </form>
        </div>
      </div>
    </section>

    <section class="rounded-3xl border border-border/70 bg-card shadow-soft">
      <div class="space-y-6 p-6">
        <div>
          <h2 class="text-2xl font-semibold">Інші команди</h2>
          <p class="text-sm text-muted-foreground">Додаткові корисні команди для керування застосунком.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <form method="POST" action="{{ route('artisan.storage.link') }}">
            @csrf
            <button type="submit" class="w-full inline-flex items-center justify-center rounded-2xl bg-accent px-5 py-3 text-sm font-semibold text-accent-foreground shadow-soft hover:bg-accent/90">
              <i class="fa-solid fa-link mr-2"></i>
              Створити symbolic link для storage
            </button>
          </form>
        </div>
      </div>
    </section>
  </div>
@endsection
