@extends('layouts.app')

@section('title', 'Структура бази даних')

@section('content')
  <div class="space-y-8" x-data="databaseStructureViewer(@js($structure), @js(route('database-structure.records', ['table' => '__TABLE__'])))">
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
            <div class="border-t border-border/60 px-6 py-5">
              <div class="flex flex-wrap items-center gap-3">
                <button
                  type="button"
                  class="inline-flex items-center gap-2 rounded-full border border-primary/40 bg-primary/10 px-4 py-2 text-xs font-semibold text-primary transition hover:bg-primary/20 focus:outline-none focus:ring-2 focus:ring-primary/40"
                  @click.stop="toggleRecords(table)"
                  x-text="table.records.visible ? 'Сховати записи' : 'Показати записи'"
                ></button>
                <template x-if="table.records.loading">
                  <span class="text-xs text-muted-foreground">Завантаження записів...</span>
                </template>
                <template x-if="table.records.error">
                  <span class="text-xs text-rose-600" x-text="table.records.error"></span>
                </template>
              </div>

              <div x-show="table.records.visible" x-collapse class="mt-4 overflow-x-auto">
                <template x-if="table.records.rows.length === 0 && !table.records.loading && !table.records.error">
                  <div class="rounded-2xl border border-dashed border-border/60 bg-muted/20 p-6 text-center text-xs text-muted-foreground">
                    Записів не знайдено.
                  </div>
                </template>

                <template x-if="table.records.rows.length > 0">
                  <table class="min-w-full divide-y divide-border/60 text-sm">
                    <thead class="bg-muted/40 text-left text-xs uppercase tracking-wider text-muted-foreground">
                      <tr>
                        <template x-for="column in table.records.columns" :key="column">
                          <th class="px-3 py-2 font-medium">
                            <button
                              type="button"
                              class="flex items-center gap-2 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground transition hover:text-primary"
                              @click.stop="toggleSort(table, column)"
                            >
                              <span x-text="column"></span>
                              <span class="text-[10px]" x-show="table.records.sort === column">
                                <i
                                  class="fa-solid"
                                  :class="table.records.direction === 'asc' ? 'fa-arrow-up-short-wide' : 'fa-arrow-down-wide-short'"
                                ></i>
                              </span>
                            </button>
                          </th>
                        </template>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-border/60">
                      <template x-for="(row, rowIndex) in table.records.rows" :key="rowIndex">
                        <tr class="hover:bg-muted/40">
                          <template x-for="column in table.records.columns" :key="column">
                            <td class="px-3 py-2 text-xs text-foreground" x-text="formatCell(row[column])"></td>
                          </template>
                        </tr>
                      </template>
                    </tbody>
                  </table>
                </template>

                <template x-if="table.records.rows.length > 0">
                  <div class="mt-4 flex flex-col gap-4 text-xs text-muted-foreground md:flex-row md:items-center md:justify-between">
                    <div>
                      Всього записів: <span class="font-semibold text-foreground" x-text="table.records.total"></span>
                    </div>
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-4">
                      <label class="flex items-center gap-2">
                        <span>На сторінці:</span>
                        <select
                          class="rounded-xl border border-input bg-background px-3 py-1 text-xs focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
                          :value="table.records.perPage"
                          @change="changePerPage(table, $event.target.value)"
                        >
                          <option value="10">10</option>
                          <option value="20">20</option>
                          <option value="50">50</option>
                          <option value="100">100</option>
                        </select>
                      </label>
                      <div class="flex items-center gap-2">
                        <button
                          type="button"
                          class="inline-flex items-center gap-1 rounded-full border border-border/60 px-3 py-1 font-medium text-muted-foreground transition hover:border-primary/60 hover:text-primary disabled:cursor-not-allowed disabled:opacity-50"
                          :disabled="table.records.page <= 1"
                          @click="changePage(table, table.records.page - 1)"
                        >
                          <i class="fa-solid fa-chevron-left text-[10px]"></i>
                          Попередня
                        </button>
                        <span>
                          Сторінка <span class="font-semibold text-foreground" x-text="table.records.page"></span>
                          з <span class="font-semibold text-foreground" x-text="table.records.lastPage"></span>
                        </span>
                        <button
                          type="button"
                          class="inline-flex items-center gap-1 rounded-full border border-border/60 px-3 py-1 font-medium text-muted-foreground transition hover:border-primary/60 hover:text-primary disabled:cursor-not-allowed disabled:opacity-50"
                          :disabled="table.records.page >= table.records.lastPage"
                          @click="changePage(table, table.records.page + 1)"
                        >
                          Наступна
                          <i class="fa-solid fa-chevron-right text-[10px]"></i>
                        </button>
                      </div>
                    </div>
                  </div>
                </template>
              </div>
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
      Alpine.data('databaseStructureViewer', (tables, recordsRoute) => ({
        query: '',
        recordsRoute,
        tables: tables.map((table) => ({
          ...table,
          open: false,
          records: {
            visible: false,
            loading: false,
            loaded: false,
            rows: [],
            columns: table.columns.map((column) => column.name),
            error: null,
            page: 1,
            perPage: 20,
            total: 0,
            lastPage: 1,
            sort: null,
            direction: 'asc',
          },
        })),
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
        async toggleRecords(table) {
          table.records.visible = !table.records.visible;
          table.records.error = null;

          if (table.records.visible && !table.records.loaded) {
            await this.loadRecords(table);
          }
        },
        async loadRecords(table) {
          table.records.loading = true;
          table.records.error = null;
          table.records.loaded = false;

          try {
            const url = new URL(
              this.recordsRoute.replace('__TABLE__', encodeURIComponent(table.name)),
              window.location.origin
            );

            url.searchParams.set('page', table.records.page);
            url.searchParams.set('per_page', table.records.perPage);

            if (table.records.sort) {
              url.searchParams.set('sort', table.records.sort);
              url.searchParams.set('direction', table.records.direction);
            }

            const response = await fetch(url.toString(), {
              headers: {
                Accept: 'application/json',
              },
            });

            if (!response.ok) {
              throw new Error('Не вдалося завантажити записи.');
            }

            const data = await response.json();
            table.records.rows = data.rows || [];
            table.records.columns = data.columns || table.records.columns;
            table.records.page = data.page || 1;
            table.records.perPage = data.per_page || table.records.perPage;
            table.records.total = data.total ?? table.records.total;
            table.records.lastPage = data.last_page || 1;
            table.records.sort = data.sort || null;
            table.records.direction = data.direction || table.records.direction;
            table.records.loaded = true;
          } catch (error) {
            table.records.error = error.message ?? 'Сталася помилка під час завантаження записів.';
          } finally {
            table.records.loading = false;
          }
        },
        changePage(table, page) {
          if (table.records.loading) {
            return;
          }

          const target = Math.min(Math.max(page, 1), table.records.lastPage || 1);

          if (target === table.records.page) {
            return;
          }

          table.records.page = target;
          this.loadRecords(table);
        },
        changePerPage(table, perPage) {
          if (table.records.loading) {
            return;
          }

          table.records.perPage = Number(perPage) || table.records.perPage;
          table.records.page = 1;
          this.loadRecords(table);
        },
        toggleSort(table, column) {
          if (table.records.loading) {
            return;
          }

          if (table.records.sort === column) {
            table.records.direction = table.records.direction === 'asc' ? 'desc' : 'asc';
          } else {
            table.records.sort = column;
            table.records.direction = 'asc';
          }

          table.records.page = 1;
          this.loadRecords(table);
        },
        formatCell(value) {
          if (value === null || value === undefined) {
            return '—';
          }

          if (typeof value === 'object') {
            try {
              return JSON.stringify(value);
            } catch (error) {
              return '[object]';
            }
          }

          return String(value);
        },
      }));
    });
  </script>
@endpush
