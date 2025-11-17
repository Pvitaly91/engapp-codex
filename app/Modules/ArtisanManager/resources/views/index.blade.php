@extends('layouts.app')

@section('title', 'Менеджер Artisan команд')

@section('content')
  <div class="max-w-4xl mx-auto space-y-8">
    <header class="space-y-2 text-center">
      <h1 class="text-3xl font-semibold">Менеджер Artisan команд</h1>
      <p class="text-muted-foreground">Виконуйте Artisan команди безпосередньо з адмін-панелі для керування кешем та оптимізації додатку.</p>
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

    <div class="grid gap-6 md:grid-cols-2">
      @foreach($commands as $commandKey => $commandData)
        <section class="rounded-3xl border border-border/70 bg-card shadow-soft">
          <div class="space-y-4 p-6">
            <div>
              <h2 class="text-xl font-semibold">{{ $commandData['title'] }}</h2>
              <p class="text-sm text-muted-foreground">{!! $commandData['description'] !!}</p>
            </div>
            <form method="POST" action="{{ route('artisan.execute', $commandKey) }}">
              @csrf
              <button 
                type="submit" 
                class="inline-flex items-center justify-center rounded-2xl {{ $commandData['button_class'] }} px-5 py-2 text-sm font-semibold text-primary-foreground shadow-soft hover:opacity-90"
                onclick="return confirm('Ви впевнені, що хочете виконати цю команду?');"
              >
                <i class="fa-solid {{ $commandData['icon'] }} mr-2"></i>
                Виконати
              </button>
            </form>
          </div>
        </section>
      @endforeach
    </div>

    <section class="rounded-3xl border border-border/70 bg-card shadow-soft">
      <div class="space-y-4 p-6">
        <div>
          <h2 class="text-2xl font-semibold">Рекомендації</h2>
          <div class="mt-3 space-y-2 text-sm text-muted-foreground">
            <p>
              <strong>Під час розробки:</strong> використовуйте "Скасувати оптимізацію" та команди очищення кешів для негайного відображення змін у коді.
            </p>
            <p>
              <strong>У продакшені:</strong> використовуйте "Оптимізувати додаток" після розгортання нової версії для максимальної продуктивності.
            </p>
            <p>
              <strong>Увага:</strong> Ці команди впливають на всіх користувачів додатку. Використовуйте їх обережно у продакшені.
            </p>
          </div>
        </div>
      </div>
    </section>
  </div>
@endsection
