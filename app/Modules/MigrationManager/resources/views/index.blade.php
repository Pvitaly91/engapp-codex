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
        <form method="GET" action="{{ route('migrations.index') }}" class="grid gap-4 md:grid-cols-4">
          <div class="md:col-span-2">
            <label class="text-xs font-semibold uppercase tracking-wide text-muted-foreground" for="migration-search">Пошук</label>
            <input id="migration-search" type="text" name="search" value="{{ $search }}" placeholder="Назва міграції"
                   class="mt-1 w-full rounded-2xl border border-border/70 bg-background/80 px-4 py-2 text-sm focus:border-primary focus:ring-0" />
          </div>
          <div>
            <label class="text-xs font-semibold uppercase tracking-wide text-muted-foreground" for="migration-sort">Сортувати за</label>
            <select id="migration-sort" name="sort" class="mt-1 w-full rounded-2xl border border-border/70 bg-background/80 px-4 py-2 text-sm focus:border-primary focus:ring-0">
              <option value="batch" @selected($sortField === 'batch')>Номер партії</option>
              <option value="migration" @selected($sortField === 'migration')>Назва</option>
            </select>
          </div>
          <div>
            <label class="text-xs font-semibold uppercase tracking-wide text-muted-foreground" for="migration-direction">Напрямок</label>
            <select id="migration-direction" name="direction" class="mt-1 w-full rounded-2xl border border-border/70 bg-background/80 px-4 py-2 text-sm focus:border-primary focus:ring-0">
              <option value="desc" @selected($sortDirection === 'desc')>Спадаюче</option>
              <option value="asc" @selected($sortDirection === 'asc')>Зростаюче</option>
            </select>
          </div>
          <div class="md:col-span-4 flex items-end justify-end">
            <button type="submit" class="inline-flex items-center rounded-2xl bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground shadow-soft hover:bg-primary/90">
              <i class="fa-solid fa-filter mr-2"></i>
              Застосувати
            </button>
            @if($search || $sortField !== 'batch' || $sortDirection !== 'desc')
              <a href="{{ route('migrations.index') }}" class="ml-3 inline-flex items-center rounded-2xl border border-border/60 px-4 py-2 text-sm font-semibold text-muted-foreground hover:text-foreground">
                Скинути
              </a>
            @endif
          </div>
        </form>
        @if($executedMigrations->isEmpty())
          <p class="text-sm text-muted-foreground">Таблиця міграцій порожня.</p>
        @else
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-border/70 text-sm">
              <thead class="bg-muted/40 text-left text-xs uppercase tracking-wide text-muted-foreground">
                <tr>
                  <th class="px-4 py-3">Назва</th>
                  <th class="px-4 py-3">Партія</th>
                  @if($fileManagerAvailable)
                    <th class="px-4 py-3">Файл</th>
                  @endif
                  <th class="px-4 py-3 text-right">Дії</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-border/60 bg-background/60">
                @foreach($executedMigrations as $migration)
                  <tr>
                    <td class="px-4 py-3 font-medium">{{ $migration->migration }}</td>
                    <td class="px-4 py-3">#{{ $migration->batch }}</td>
                    @if($fileManagerAvailable)
                      <td class="px-4 py-3 text-xs">
                        @php($paths = $executedMigrationPaths[$migration->migration] ?? null)
                        @if($paths)
                          <div class="flex flex-col gap-1">
                            <a href="{{ route('file-manager.index', array_filter(['path' => $paths['directory'] ?? null, 'select' => $paths['file'] ?? null], fn ($value) => $value !== null)) }}" class="text-primary hover:text-primary/80" target="_blank">
                              Переглянути файл
                            </a>
                            <a href="{{ route('file-manager.index', ['path' => $paths['directory']]) }}" class="text-primary hover:text-primary/80" target="_blank">
                              Відкрити папку
                            </a>
                          </div>
                        @else
                          <span class="text-muted-foreground">Файл відсутній</span>
                        @endif
                      </td>
                    @endif
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
