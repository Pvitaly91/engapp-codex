@extends('layouts.app')

@section('title', 'Структура бази даних')

@section('content')
  <div class="space-y-8" x-data="databaseStructureViewer(@js($structure))">
    <header class="rounded-3xl border border-border/70 bg-card/80 p-6 shadow-soft">
      <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
        <div class="space-y-3">
          <p class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-widest text-muted-foreground">
            <span class="inline-flex h-2.5 w-2.5 rounded-full bg-primary"></span>
            Інструмент адміністрування
          </p>
          <div class="space-y-2">
            <h1 class="text-3xl font-semibold text-foreground">Структура бази даних</h1>
            <p class="max-w-2xl text-sm text-muted-foreground">
              Цей модуль відображає таблиці поточного з'єднання Laravel, їх поля та типи.
              Ви можете використати його на будь-якому проєкті, просто підключивши сервіс-провайдер.
            </p>
          </div>
        </div>
        <dl class="grid flex-shrink-0 grid-cols-2 gap-4 text-sm">
          <div class="rounded-2xl border border-border/70 bg-background/60 p-4 shadow-soft/30">
            <dt class="text-muted-foreground">Підключення</dt>
            <dd class="mt-1 font-semibold text-foreground">{{ $meta['connection'] }} ({{ $meta['driver'] }})</dd>
          </div>
          <div class="rounded-2xl border border-border/70 bg-background/60 p-4 shadow-soft/30">
            <dt class="text-muted-foreground">База даних</dt>
            <dd class="mt-1 font-semibold text-foreground">{{ $meta['database'] ?? '—' }}</dd>
          </div>
          <div class="rounded-2xl border border-border/70 bg-background/60 p-4 shadow-soft/30">
            <dt class="text-muted-foreground">Кількість таблиць</dt>
            <dd class="mt-1 font-semibold text-foreground">{{ $meta['tables_count'] }}</dd>
          </div>
          <div class="rounded-2xl border border-border/70 bg-background/60 p-4 shadow-soft/30">
            <dt class="text-muted-foreground">Кількість полів</dt>
            <dd class="mt-1 font-semibold text-foreground">{{ $meta['columns_count'] }}</dd>
          </div>
        </dl>
      </div>
      <div class="mt-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div class="text-xs text-muted-foreground">
          Натисніть на назву таблиці, щоб розгорнути або згорнути деталі.
        </div>
        <div class="relative w-full md:w-80">
          <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-muted-foreground">
            <i class="fa-solid fa-magnifying-glass text-sm"></i>
          </span>
          <input
            type="search"
            placeholder="Пошук за таблицями або полями..."
            class="w-full rounded-2xl border border-input bg-background py-2 pl-10 pr-4 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
            x-model.trim="query"
          />
        </div>
      </div>
    </header>

    <template x-if="filteredTables.length === 0">
      <div class="rounded-3xl border border-dashed border-border/60 bg-muted/30 p-10 text-center text-sm text-muted-foreground">
        Нічого не знайдено. Змініть пошуковий запит або скиньте фільтр.
      </div>
    </template>

    <div class="space-y-6">
      <template x-for="table in filteredTables" :key="table.name">
        <section class="rounded-3xl border border-border/70 bg-card shadow-soft">
          <header
            class="flex flex-col gap-4 border-b border-border/60 px-6 py-5 sm:flex-row sm:items-center sm:justify-between"
            @click="table.open = !table.open"
          >
            <div class="space-y-1">
              <div class="flex items-center gap-3">
                <h2 class="text-xl font-semibold text-foreground" x-text="table.name"></h2>
                <span class="rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary" x-text="table.columns.length + ' полів'"></span>
              </div>
              <p class="text-sm text-muted-foreground" x-show="table.comment" x-text="table.comment"></p>
            </div>
            <div class="flex items-center gap-3">
              <template x-if="table.engine">
                <span class="rounded-full border border-border/70 bg-background px-3 py-1 text-xs font-medium text-muted-foreground" x-text="table.engine"></span>
              </template>
              <i class="fa-solid fa-chevron-down text-muted-foreground transition-transform duration-200" :class="{ 'rotate-180': table.open }"></i>
            </div>
          </header>
          <div x-show="table.open" x-collapse>
            <div class="overflow-x-auto px-6 py-5">
              <table class="min-w-full divide-y divide-border/60 text-sm">
                <thead class="text-left text-xs uppercase tracking-wider text-muted-foreground">
                  <tr>
                    <th class="pb-3 pr-4 font-medium">Поле</th>
                    <th class="pb-3 pr-4 font-medium">Тип</th>
                    <th class="pb-3 pr-4 font-medium">Null</th>
                    <th class="pb-3 pr-4 font-medium">За замовчуванням</th>
                    <th class="pb-3 pr-4 font-medium">Ключ</th>
                    <th class="pb-3 pr-4 font-medium">Додатково</th>
                    <th class="pb-3 font-medium">Коментар</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-border/60 text-foreground">
                  <template x-for="column in table.columns" :key="column.name">
                    <tr class="hover:bg-muted/40">
                      <td class="py-2 pr-4 font-medium" x-text="column.name"></td>
                      <td class="py-2 pr-4 text-muted-foreground" x-text="column.type"></td>
                      <td class="py-2 pr-4">
                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold" :class="column.nullable ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600'" x-text="column.nullable ? 'Так' : 'Ні'"></span>
                      </td>
                      <td class="py-2 pr-4 text-muted-foreground" x-text="column.default ?? '—'"></td>
                      <td class="py-2 pr-4 text-muted-foreground" x-text="column.key ?? '—'"></td>
                      <td class="py-2 pr-4 text-muted-foreground" x-text="column.extra ?? '—'"></td>
                      <td class="py-2 text-muted-foreground" x-text="column.comment ?? '—'"></td>
                    </tr>
                  </template>
                </tbody>
              </table>
            </div>
          </div>
        </section>
      </template>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    document.addEventListener('alpine:init', () => {
      Alpine.data('databaseStructureViewer', (tables) => ({
        query: '',
        tables: tables.map((table) => ({ ...table, open: false })),
        get filteredTables() {
          if (!this.query) {
            return this.tables;
          }

          const q = this.query.toLowerCase();
          return this.tables.filter((table) => {
            if (table.name.toLowerCase().includes(q)) {
              return true;
            }

            return table.columns.some((column) =>
              column.name.toLowerCase().includes(q) ||
              (column.type && column.type.toLowerCase().includes(q))
            );
          });
        },
      }));
    });
  </script>
@endpush
