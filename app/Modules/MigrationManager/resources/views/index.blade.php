@extends('layouts.app')

@section('title', 'Менеджер міграцій')

@section('content')
  <div class="max-w-4xl mx-auto space-y-8">
    <header class="space-y-2 text-center">
      <h1 class="text-3xl font-semibold">Керування міграціями</h1>
      <p class="text-muted-foreground">Запускайте нові міграції або відкатуйте останню партію безпосередньо з адмін-панелі.</p>
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
          <h2 class="text-2xl font-semibold">Виконати всі нові міграції</h2>
          <p class="text-sm text-muted-foreground">Команда <code>php artisan migrate --force</code> буде запущена на сервері. Всі незастосовані міграції будуть виконані.</p>
        </div>
        <form method="POST" action="{{ route('migrations.run') }}" class="space-y-3">
          @csrf
          <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-primary px-5 py-2 text-sm font-semibold text-primary-foreground shadow-soft hover:bg-primary/90">
            <i class="fa-solid fa-database mr-2"></i>
            Запустити міграції
          </button>
        </form>
      </div>
    </section>

    <section class="rounded-3xl border border-border/70 bg-card shadow-soft">
      <div class="space-y-6 p-6">
        <div>
          <h2 class="text-2xl font-semibold">Відкотити останню партію</h2>
          <p class="text-sm text-muted-foreground">Запускається <code>php artisan migrate:rollback --step=1 --force</code>. Це поверне останні застосовані міграції.</p>
        </div>
        <form method="POST" action="{{ route('migrations.rollback') }}" class="space-y-3">
          @csrf
          <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-warning px-5 py-2 text-sm font-semibold text-foreground shadow-soft hover:bg-warning/90">
            <i class="fa-solid fa-rotate-left mr-2"></i>
            Відкотити останню партію
          </button>
        </form>
      </div>
    </section>

    <section class="rounded-3xl border border-border/70 bg-card shadow-soft">
      <div class="space-y-6 p-6">
        <div>
          <h2 class="text-2xl font-semibold">Незастосовані міграції</h2>
          <p class="text-sm text-muted-foreground">Список файлів, які ще не були виконані. Після запуску міграцій він спорожніє.</p>
        </div>
        @if($pendingMigrations->isEmpty())
          <p class="text-sm text-muted-foreground">Усі міграції виконані. Нових файлів не знайдено.</p>
        @else
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-border/70 text-sm">
              <thead class="bg-muted/40 text-left text-xs uppercase tracking-wide text-muted-foreground">
                <tr>
                  <th class="px-4 py-3">Назва</th>
                  <th class="px-4 py-3">Шлях</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-border/60 bg-background/60">
                @foreach($pendingMigrations as $migration)
                  <tr>
                    <td class="px-4 py-3 font-medium">{{ $migration['name'] }}</td>
                    <td class="px-4 py-3 text-xs font-mono">{{ $migration['path'] }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>
    </section>

    <section class="rounded-3xl border border-border/70 bg-card shadow-soft">
      <div class="space-y-6 p-6">
        <div>
          <h2 class="text-2xl font-semibold">Остання виконана партія</h2>
          <p class="text-sm text-muted-foreground">Дані з таблиці <code>migrations</code> для останнього значення <code>batch</code>.</p>
        </div>
        @if($lastBatch->isEmpty())
          <p class="text-sm text-muted-foreground">Ще не було виконано жодної міграції.</p>
        @else
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-border/70 text-sm">
              <thead class="bg-muted/40 text-left text-xs uppercase tracking-wide text-muted-foreground">
                <tr>
                  <th class="px-4 py-3">Назва</th>
                  <th class="px-4 py-3">Партія</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-border/60 bg-background/60">
                @foreach($lastBatch as $migration)
                  <tr>
                    <td class="px-4 py-3 font-medium">{{ $migration->migration }}</td>
                    <td class="px-4 py-3">#{{ $migration->batch }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>
    </section>

    <section class="rounded-3xl border border-border/70 bg-card shadow-soft">
      <div class="space-y-6 p-6">
        <div>
          <h2 class="text-2xl font-semibold">Всі виконані міграції</h2>
          <p class="text-sm text-muted-foreground">Список записів у таблиці <code>migrations</code> з можливістю видалення окремих рядків.</p>
        </div>
        @if($executedMigrations->isEmpty())
          <p class="text-sm text-muted-foreground">Таблиця міграцій порожня.</p>
        @else
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-border/70 text-sm">
              <thead class="bg-muted/40 text-left text-xs uppercase tracking-wide text-muted-foreground">
                <tr>
                  <th class="px-4 py-3">Назва</th>
                  <th class="px-4 py-3">Партія</th>
                  <th class="px-4 py-3 text-right">Дії</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-border/60 bg-background/60">
                @foreach($executedMigrations as $migration)
                  <tr>
                    <td class="px-4 py-3 font-medium">{{ $migration->migration }}</td>
                    <td class="px-4 py-3">#{{ $migration->batch }}</td>
                    <td class="px-4 py-3">
                      <form method="POST" action="{{ route('migrations.destroy', $migration->migration) }}" class="flex justify-end" onsubmit="return confirm('Видалити запис про міграцію?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center rounded-xl bg-destructive/80 px-3 py-1 text-xs font-semibold text-destructive-foreground shadow-soft hover:bg-destructive">
                          <i class="fa-solid fa-trash mr-2"></i>
                          Видалити
                        </button>
                      </form>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>
    </section>
  </div>
@endsection
