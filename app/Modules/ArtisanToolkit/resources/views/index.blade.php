@extends('layouts.app')

@section('title', 'Artisan Toolkit')

@section('content')
  <div class="max-w-5xl mx-auto space-y-8">
    <header class="space-y-2 text-center">
      <p class="inline-flex items-center rounded-full bg-blue-50 px-4 py-1 text-xs font-semibold uppercase tracking-wide text-blue-700">
        <i class="fa-solid fa-rocket mr-2"></i>
        Системні інструменти
      </p>
      <h1 class="text-3xl font-semibold">Artisan Toolkit</h1>
      <p class="text-muted-foreground">Запускайте типові команди <code>php artisan</code> однією кнопкою прямо з адмінки.</p>
    </header>

    @if($feedback)
      <div @class([
        'rounded-2xl border p-4 shadow-soft',
        'border-success/40 bg-success/10 text-success' => $feedback['status'] === 'success',
        'border-destructive/40 bg-destructive/10 text-destructive-foreground' => $feedback['status'] === 'error',
      ])>
        <div class="font-medium flex items-center gap-2">
          <i class="fa-solid {{ $feedback['status'] === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' }}"></i>
          {{ $feedback['message'] }}
        </div>
        @if(! empty($feedback['output']))
          <pre class="mt-3 max-h-64 overflow-y-auto whitespace-pre-wrap rounded-xl border border-border/70 bg-background/70 p-3 text-xs leading-relaxed">{{ $feedback['output'] }}</pre>
        @endif
      </div>
    @endif

    <div class="grid gap-5 md:grid-cols-2">
      @foreach($commands as $command)
        <section class="rounded-3xl border border-border/70 bg-card shadow-soft">
          <div class="space-y-4 p-6">
            <div class="flex items-start justify-between gap-3">
              <div class="space-y-1">
                <h2 class="text-xl font-semibold">{{ $command['title'] }}</h2>
                <p class="text-sm text-muted-foreground">{{ $command['description'] }}</p>
              </div>
              <span class="rounded-full bg-muted px-3 py-1 text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">
                php artisan {{ $command['command'] }}
              </span>
            </div>

            @if(! empty($command['note']))
              <div class="rounded-2xl bg-muted/60 px-3 py-2 text-xs text-muted-foreground">{{ $command['note'] }}</div>
            @endif

            <form method="POST" action="{{ route('artisan-toolkit.run', $command['key']) }}" class="flex flex-wrap items-center gap-3">
              @csrf
              <button type="submit" class="inline-flex items-center rounded-2xl bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground shadow-soft hover:bg-primary/90">
                <i class="fa-solid fa-bolt mr-2"></i>
                {{ $command['button_label'] ?? 'Запустити' }}
              </button>
              @if(! empty($command['success_message']))
                <span class="text-xs text-muted-foreground">Очікуваний результат: {{ $command['success_message'] }}</span>
              @endif
            </form>
          </div>
        </section>
      @endforeach
    </div>
  </div>
@endsection
