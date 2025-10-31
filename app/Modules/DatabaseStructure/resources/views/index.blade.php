@extends('layouts.app')

@section('title', 'Структура бази даних')

@section('content')
  @php
    $currentTab = ($activeTab ?? 'structure') === 'content-management' ? 'content-management' : 'structure';
    $standaloneTab = in_array($standaloneTab ?? null, ['structure', 'content-management'], true)
      ? $standaloneTab
      : null;
  @endphp

  <div
    class="space-y-8 px-4 sm:px-0"
    x-data="databaseStructureViewer(
      @js($structure),
      @js(route('database-structure.records', ['table' => '__TABLE__'])),
      @js(route('database-structure.destroy', ['table' => '__TABLE__'])),
      @js(route('database-structure.value', ['table' => '__TABLE__'])),
      @js(route('database-structure.record', ['table' => '__TABLE__'])),
      @js(route('database-structure.update', ['table' => '__TABLE__'])),
      @js(route('database-structure.structure', ['table' => '__TABLE__'])),
      @js(route('database-structure.manual-foreign.store', ['table' => '__TABLE__', 'column' => '__COLUMN__'])),
      @js(route('database-structure.manual-foreign.destroy', ['table' => '__TABLE__', 'column' => '__COLUMN__'])),
      @js($contentManagementMenu),
      @js([
        'menuStore' => route('database-structure.content-management.menu.store'),
        'menuDelete' => route('database-structure.content-management.menu.destroy', ['table' => '__TABLE__']),
      ]),
      @js([
        'initialTab' => $currentTab,
        'standaloneTab' => $standaloneTab,
        'tabRoutes' => [
          'structure' => route('database-structure.index'),
          'content-management' => route('database-structure.content-management'),
        ],
      ])
    )"
    @keydown.window.escape.prevent="handleEscape()"
  >
    <div class="flex justify-start">
      <div class="inline-flex w-full flex-wrap items-center gap-1 rounded-full border border-border/60 bg-background p-1 text-sm font-semibold text-muted-foreground shadow-soft/40 sm:w-auto">
        <a
          href="{{ route('database-structure.index') }}"
          class="flex-1 rounded-full px-4 py-1.5 text-center transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 sm:flex-none"
          :class="activeTab === 'structure' ? 'bg-primary text-white shadow-soft' : 'text-muted-foreground hover:text-foreground'"
          aria-current="{{ $currentTab === 'structure' ? 'page' : 'false' }}"
        >
          Структура БД
        </a>
        <a
          href="{{ route('database-structure.content-management') }}"
          class="flex-1 rounded-full px-4 py-1.5 text-center transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 sm:flex-none"
          :class="activeTab === 'content-management' ? 'bg-primary text-white shadow-soft' : 'text-muted-foreground hover:text-foreground'"
          aria-current="{{ $currentTab === 'content-management' ? 'page' : 'false' }}"
        >
          Content Management
        </a>
      </div>
    </div>

    <div x-show="activeTab === 'structure'" x-cloak class="space-y-8">
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
        <dl class="grid flex-shrink-0 grid-cols-1 gap-4 text-sm sm:grid-cols-2">
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
            class="flex flex-col gap-4 border-b border-border/60 px-6 py-5 sm:flex-row sm:items-center sm:justify-between cursor-pointer"
            @click="toggleTable(table)"
          >
            <div class="space-y-1">
              <div class="flex items-center gap-3">
                <h2 class="text-xl font-semibold text-foreground" x-html="highlightQuery(table.name)"></h2>
                <span class="rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary" x-text="table.columnsCount + ' полів'"></span>
              </div>
              <p class="text-sm text-muted-foreground" x-show="table.comment" x-html="highlightQuery(table.comment)"></p>
            </div>
            <div class="flex items-center gap-3">
              <template x-if="table.engine">
                <span class="rounded-full border border-border/70 bg-background px-3 py-1 text-xs font-medium text-muted-foreground" x-html="highlightQuery(table.engine)"></span>
              </template>
              <i class="fa-solid fa-chevron-down text-muted-foreground transition-transform duration-200" :class="{ 'rotate-180': table.open }"></i>
            </div>
          </header>
          <div x-show="table.open" x-collapse>
            <div class="px-6 py-5">
              <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-muted-foreground">Структура таблиці</h3>
                <button
                  type="button"
                  class="inline-flex items-center gap-2 rounded-full border border-border/70 bg-background px-4 py-1.5 text-xs font-semibold text-muted-foreground transition hover:border-primary/60 hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
                  @click.stop="table.structureVisible = !table.structureVisible"
                >
                  <i class="fa-solid" :class="table.structureVisible ? 'fa-eye-slash' : 'fa-eye'"></i>
                  <span x-text="table.structureVisible ? 'Сховати структуру' : 'Показати структуру'"></span>
                </button>
              </div>
              <div x-show="table.structureVisible" x-collapse>
                <div class="mt-4 space-y-3">
                  <template x-if="table.structure.loading">
                    <div class="rounded-2xl border border-dashed border-border/60 bg-muted/20 p-4 text-sm text-muted-foreground">
                      Завантаження структури...
                    </div>
                  </template>
                  <template x-if="!table.structure.loading && table.structure.error">
                    <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-600" x-text="table.structure.error"></div>
                  </template>
                  <template x-if="!table.structure.loading && !table.structure.error && table.structure.loaded && table.structure.columns.length === 0">
                    <div class="rounded-2xl border border-dashed border-border/60 bg-muted/20 p-4 text-sm text-muted-foreground">
                      Структуру таблиці не знайдено.
                    </div>
                  </template>
                  <template x-if="!table.structure.loading && table.structure.columns.length > 0">
                    <div class="overflow-x-auto">
                      <table class="min-w-full divide-y divide-border/60 text-[15px]">
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
                        <tbody class="divide-y divide-border/60 text-[15px] text-foreground">
                          <template x-for="column in table.structure.columns" :key="column.name">
                            <tr class="hover:bg-muted/40 transition">
                              <td class="py-2 pr-4 font-medium">
                                <div class="flex flex-col gap-1">
                                  <span x-html="highlightQuery(column.name)"></span>
                                  <template x-if="column.foreign">
                                    <span class="inline-flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                                      <i class="fa-solid fa-link" :class="column.foreign.manual ? 'text-amber-500' : 'text-primary'"></i>
                                      <span>
                                        <span x-text="column.name"></span>
                                        <span> -&gt; </span>
                                        <span x-text="column.foreign.table"></span>
                                        <span>.</span>
                                        <span x-text="column.foreign.column"></span>
                                      </span>
                                      <template x-if="column.foreign.manual">
                                        <span class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-2 py-0.5 text-[10px] font-semibold text-amber-600">
                                          Ручний зв'язок
                                        </span>
                                      </template>
                                    </span>
                                  </template>
                                  <template x-if="(!column.foreign || column.foreign.manual) && manualForeignRoutes.store">
                                    <div class="flex flex-wrap items-center gap-2 text-xs">
                                      <button
                                        type="button"
                                        class="inline-flex items-center gap-2 rounded-full border border-border/60 bg-background px-3 py-1 text-[11px] font-semibold text-muted-foreground transition hover:border-primary/60 hover:text-primary focus:outline-none focus:ring-1 focus:ring-primary/40"
                                        @click.stop="openManualForeignModal(table, column)"
                                      >
                                        <i class="fa-solid fa-plug text-[10px]"></i>
                                        <span x-text="column.foreign && column.foreign.manual ? 'Змінити ручний зв\'язок' : 'Налаштувати зв\'язок'"></span>
                                      </button>
                                      <template x-if="column.foreign && column.foreign.manual && manualForeignRoutes.delete">
                                        <button
                                          type="button"
                                          class="inline-flex items-center gap-2 rounded-full border border-border/60 bg-background px-3 py-1 text-[11px] font-semibold text-rose-600 transition hover:border-rose-300 hover:bg-rose-50 focus:outline-none focus:ring-1 focus:ring-rose-200/70"
                                          @click.stop="confirmManualForeignRemoval(table, column)"
                                        >
                                          <i class="fa-solid fa-trash text-[10px]"></i>
                                          Видалити
                                        </button>
                                      </template>
                                    </div>
                                  </template>
                                  <template x-if="getManualForeignError(table.name, column.name)">
                                    <div class="text-xs text-rose-600" x-text="getManualForeignError(table.name, column.name)"></div>
                                  </template>
                                </div>
                              </td>
                              <td class="py-2 pr-4 text-muted-foreground" x-html="highlightQuery(column.type)"></td>
                              <td class="py-2 pr-4">
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold" :class="column.nullable ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600'" x-text="column.nullable ? 'Так' : 'Ні'"></span>
                              </td>
                              <td class="py-2 pr-4 text-muted-foreground" x-html="highlightQuery(column.default ?? '—')"></td>
                              <td class="py-2 pr-4 text-muted-foreground" x-html="highlightQuery(column.key ?? '—')"></td>
                              <td class="py-2 pr-4 text-muted-foreground" x-html="highlightQuery(column.extra ?? '—')"></td>
                              <td class="py-2 text-muted-foreground" x-html="highlightQuery(column.comment ?? '—')"></td>
                            </tr>
                            <template x-if="column.foreign && (column.foreign.constraint || column.foreign.displayColumn || column.foreign.manual)">
                              <tr>
                                <td colspan="7" class="bg-primary/5 px-6 py-3 text-sm text-muted-foreground">
                                  <div class="flex items-start gap-3">
                                    <span class="mt-0.5 inline-flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary">
                                      <i class="fa-solid fa-database"></i>
                                    </span>
                                    <div class="space-y-1">
                                      <template x-if="column.foreign.manual">
                                        <div>
                                          Тип зв'язку:
                                          <span class="font-medium text-amber-600">Ручний (конфігурація)</span>
                                        </div>
                                      </template>
                                      <template x-if="column.foreign.constraint">
                                        <div>
                                          Обмеження:
                                          <span class="font-medium text-foreground" x-text="column.foreign.constraint"></span>
                                        </div>
                                      </template>
                                      <template x-if="column.foreign.displayColumn">
                                        <div>
                                          Колонка для відображення:
                                          <span class="font-medium text-foreground" x-text="column.foreign.displayColumn"></span>
                                        </div>
                                      </template>
                                    </div>
                                  </div>
                                </td>
                              </tr>
                            </template>
                          </template>
                        </tbody>
                      </table>
                    </div>
                  </template>
                </div>
              </div>
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

              <div x-show="table.records.visible" x-collapse class="mt-4 space-y-4">
                <div class="rounded-2xl border border-border/60 bg-muted/20 p-4 text-[15px] text-muted-foreground">
                  <div class="flex flex-col gap-4">
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                      <h3 class="text-sm font-semibold text-foreground">Фільтри записів</h3>
                      <div class="flex flex-wrap items-center gap-2 text-[15px]">
                        <button
                          type="button"
                          class="inline-flex items-center gap-2 rounded-full border border-border/70 bg-background px-4 py-1.5 text-[15px] font-semibold text-foreground transition hover:border-primary/60 hover:text-primary"
                          @click.stop="addFilter(table)"
                          :disabled="table.records.loading"
                        >
                          <i class="fa-solid fa-plus text-[10px]"></i>
                          Додати фільтр
                        </button>
                        <button
                          type="button"
                          class="inline-flex items-center gap-2 rounded-full border border-border/70 bg-background px-4 py-1.5 text-[15px] font-semibold text-foreground transition hover:border-primary/60 hover:text-primary disabled:cursor-not-allowed disabled:opacity-60"
                          :disabled="table.records.filters.length === 0 || table.records.loading"
                          @click.stop="resetFilters(table)"
                        >
                          <i class="fa-solid fa-rotate-left text-[10px]"></i>
                          Скинути
                        </button>
                        <button
                          type="button"
                          class="inline-flex items-center gap-2 rounded-full border border-primary/40 bg-primary/10 px-4 py-1.5 text-[15px] font-semibold text-primary transition hover:bg-primary/20 disabled:cursor-not-allowed disabled:opacity-60"
                          :disabled="table.records.loading"
                          @click.stop="applyFilters(table)"
                        >
                          <i class="fa-solid fa-filter text-[10px]"></i>
                          Застосувати
                        </button>
                      </div>
                    </div>
                    <div class="flex w-full flex-col gap-2 text-[13px] font-semibold uppercase tracking-wide text-muted-foreground/80">
                      <span class="text-[12px] font-semibold uppercase tracking-wide text-muted-foreground">Пошук записів</span>
                      <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-3">
                        <div class="relative flex-1">
                          <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-muted-foreground">
                            <i class="fa-solid fa-magnifying-glass text-xs"></i>
                          </span>
                          <input
                            type="search"
                            class="w-full rounded-xl border border-input bg-background py-2 pl-9 pr-4 text-[15px] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
                            placeholder="Миттєвий пошук за вибраною колонкою або всіма..."
                            x-model="table.records.searchInput"
                            @input.debounce.500ms="updateSearch(table, $event.target.value)"
                          />
                        </div>
                        <label class="flex flex-col gap-1 text-[12px] font-semibold uppercase tracking-wide text-muted-foreground sm:w-48">
                          <span>Колонка для пошуку</span>
                          <select
                            class="rounded-xl border border-input bg-background px-3 py-2 text-[15px] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40 disabled:cursor-not-allowed disabled:opacity-75"
                            :disabled="table.records.loading || !table.records.columns || table.records.columns.length === 0"
                            :value="table.records.searchColumn"
                            @change="updateSearchColumn(table, $event.target.value)"
                          >
                            <option value="">Всі колонки</option>
                            <template x-for="column in table.records.columns" :key="column + '-search-option'">
                              <option :value="column" x-text="column"></option>
                            </template>
                          </select>
                        </label>
                      </div>
                    </div>
                  </div>
                  <p class="mt-2 text-[15px] text-muted-foreground">
                    Використовуйте фільтри, щоб обмежити записи за значеннями колонок. Для операторів LIKE можна застосовувати символи
                    <code class="rounded bg-muted px-1">%</code> та <code class="rounded bg-muted px-1">_</code>.
                  </p>
                  <template x-if="table.records.filters.length === 0">
                    <div class="mt-3 rounded-xl border border-dashed border-border/60 bg-background/60 p-4 text-[15px] text-muted-foreground">
                      Фільтри не задано. Додайте новий, щоб відфільтрувати записи.
                    </div>
                  </template>
                  <div class="mt-3 space-y-3 text-[15px]" x-show="table.records.filters.length > 0">
                    <template x-for="(filter, filterIndex) in table.records.filters" :key="filter.id">
                      <div class="flex flex-col gap-3 rounded-xl border border-border/60 bg-background/70 p-4 text-[15px] sm:flex-row sm:items-end sm:gap-4">
                        <div class="flex flex-1 flex-col gap-3 text-[15px] sm:flex-row sm:items-end sm:gap-4">
                          <label class="flex flex-1 flex-col gap-1 text-[15px] font-semibold uppercase tracking-wide text-muted-foreground/80">
                            <span>Поле</span>
                            <select
                              class="rounded-xl border border-input bg-background px-3 py-2 text-[15px] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
                              x-model="filter.column"
                              :disabled="table.records.loading"
                            >
                              <option value="">Оберіть поле</option>
                              <template x-for="column in table.records.columns" :key="column + '-filter-option'">
                                <option :value="column" x-text="column"></option>
                              </template>
                            </select>
                          </label>
                          <label class="flex w-full flex-col gap-1 text-[15px] font-semibold uppercase tracking-wide text-muted-foreground/80 sm:w-48">
                            <span>Оператор</span>
                            <select
                              class="rounded-xl border border-input bg-background px-3 py-2 text-[15px] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
                              x-model="filter.operator"
                              :disabled="table.records.loading"
                            >
                              <template x-for="option in filterOperators" :key="option.value">
                                <option :value="option.value" x-text="option.label"></option>
                              </template>
                            </select>
                          </label>
                          <label
                            class="flex flex-1 flex-col gap-1 text-[15px] font-semibold uppercase tracking-wide text-muted-foreground/80"
                            x-show="operatorRequiresValue(filter.operator)"
                          >
                            <span>Значення</span>
                            <input
                              type="text"
                              class="rounded-xl border border-input bg-background px-3 py-2 text-[15px] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
                              placeholder="Вкажіть значення"
                              x-model="filter.value"
                              :disabled="table.records.loading"
                            />
                          </label>
                        </div>
                        <button
                          type="button"
                          class="inline-flex items-center justify-center gap-2 rounded-full border border-border/70 bg-background px-3 py-1.5 text-[15px] font-semibold text-muted-foreground transition hover:border-rose-300 hover:text-rose-500"
                          @click.stop="removeFilter(table, filterIndex)"
                          :disabled="table.records.loading"
                        >
                          <i class="fa-solid fa-xmark text-[10px]"></i>
                          Прибрати
                        </button>
                      </div>
                    </template>
                  </div>
                </div>

                <template x-if="table.records.rows.length === 0 && !table.records.loading && !table.records.error">
                  <div class="rounded-2xl border border-dashed border-border/60 bg-muted/20 p-6 text-center text-xs text-muted-foreground">
                    Записів не знайдено.
                  </div>
                </template>

                <template x-if="table.records.rows.length > 0">
                  <div class="space-y-4">
                    <div class="flex flex-col gap-4 text-xs text-muted-foreground md:flex-row md:items-center md:justify-between">
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

                    <div class="overflow-x-auto">
                      <table class="min-w-full divide-y divide-border/60 text-[15px]">
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
                            <th class="px-3 py-2 font-medium text-right">Дії</th>
                          </tr>
                        </thead>
                        <tbody class="divide-y divide-border/60 text-[15px]">
                          <template x-for="(row, rowIndex) in table.records.rows" :key="rowIndex">
                            <tr class="hover:bg-muted/40">
                              <template x-for="column in table.records.columns" :key="column">
                                <td class="px-3 py-2 text-[15px] text-foreground">
                                  <button
                                    type="button"
                                    class="-mx-2 -my-1 block w-full rounded-lg px-2 py-1 text-left text-[15px] text-foreground transition hover:bg-primary/5 focus:outline-none focus:ring-2 focus:ring-primary/40"
                                    @click.stop="showRecordValue(table, column, row)"
                                    :title="formatCell(row[column])"
                                    x-html="renderRecordPreview(table, column, row[column])"
                                  ></button>
                                </td>
                              </template>
                              <td class="px-3 py-2 text-right">
                                <button
                                  type="button"
                                  class="inline-flex items-center gap-2 rounded-full border border-border/70 bg-background px-3 py-1 text-xs font-semibold text-rose-500 transition hover:border-rose-300 hover:text-rose-600 disabled:cursor-not-allowed disabled:opacity-60"
                                  :disabled="table.records.loading || table.records.deletingRowIndex === rowIndex"
                                  @click.stop="deleteRecord(table, row, rowIndex)"
                                >
                                  <span x-show="table.records.deletingRowIndex !== rowIndex">Видалити</span>
                                  <span x-show="table.records.deletingRowIndex === rowIndex" x-cloak>Видалення...</span>
                                </button>
                              </td>
                            </tr>
                          </template>
                        </tbody>
                      </table>
                    </div>

                    <div class="flex flex-col gap-4 text-xs text-muted-foreground md:flex-row md:items-center md:justify-between">
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
                  </div>
                </template>
              </div>
            </div>
          </div>
          </section>
        </template>
      </div>
      <div
        x-show="manualForeignModal.open"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6"
        role="dialog"
        aria-modal="true"
      >
        <div
          class="absolute inset-0 bg-background/80 backdrop-blur-sm"
          @click="manualForeignModal.saving ? null : closeManualForeignModal()"
        ></div>
        <div class="relative z-10 w-full max-w-xl rounded-3xl border border-border/70 bg-white p-6 shadow-xl">
          <div class="flex items-start justify-between gap-4">
            <div>
              <h2 class="text-lg font-semibold text-foreground">Налаштування ручного зв'язку</h2>
              <p class="mt-1 text-xs text-muted-foreground">
                Виберіть таблицю та колонку, з якою потрібно пов'язати це поле.
              </p>
            </div>
            <button
              type="button"
              class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-border/60 text-muted-foreground transition hover:text-foreground focus:outline-none focus:ring-2 focus:ring-primary/40"
              @click="manualForeignModal.saving ? null : closeManualForeignModal()"
            >
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>
          <div class="mt-4 space-y-2 text-xs text-muted-foreground">
            <div>
              <span class="font-semibold text-foreground">Таблиця:</span>
              <span class="ml-1" x-text="manualForeignModal.sourceTable || '—'"></span>
            </div>
            <div>
              <span class="font-semibold text-foreground">Поле:</span>
              <span class="ml-1" x-text="manualForeignModal.sourceColumn || '—'"></span>
            </div>
          </div>
          <form class="mt-6 space-y-4" @submit.prevent="saveManualForeign">
            <div class="space-y-4">
              <label class="flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                <span>Таблиця призначення</span>
                <select
                  class="rounded-2xl border border-input bg-background px-4 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
                  :disabled="manualForeignModal.saving"
                  :value="manualForeignModal.targetTable"
                  @change="changeManualForeignTargetTable($event.target.value)"
                >
                  <option value="">Оберіть таблицю</option>
                  <template x-for="availableTable in tables" :key="`manual-table-${availableTable.name}`">
                    <option
                      :value="availableTable.name"
                      x-text="availableTable.name"
                    ></option>
                  </template>
                </select>
              </label>
              <label class="flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                <span>Колонка призначення</span>
                <select
                  class="rounded-2xl border border-input bg-background px-4 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
                  :disabled="manualForeignModal.saving || !manualForeignModal.targetTable"
                  :value="manualForeignModal.targetColumn"
                  @change="changeManualForeignTargetColumn($event.target.value)"
                >
                  <option value="">Оберіть колонку</option>
                  <template x-for="columnName in manualForeignModal.targetColumns" :key="`manual-column-${columnName}`">
                    <option :value="columnName" x-text="columnName"></option>
                  </template>
                </select>
              </label>
              <label class="flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                <span>Колонка для відображення</span>
                <select
                  class="rounded-2xl border border-input bg-background px-4 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
                  :disabled="manualForeignModal.saving || !manualForeignModal.targetTable"
                  :value="manualForeignModal.displayColumn"
                  @change="changeManualForeignDisplayColumn($event.target.value)"
                >
                  <option value="">Автоматично</option>
                  <template x-for="columnName in manualForeignModal.targetColumns" :key="`manual-display-${columnName}`">
                    <option :value="columnName" x-text="columnName"></option>
                  </template>
                </select>
              </label>
            </div>
            <template x-if="manualForeignModal.error">
              <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-600" x-text="manualForeignModal.error"></div>
            </template>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
              <template x-if="hasManualForeign(manualForeignModal.sourceTable, manualForeignModal.sourceColumn) && manualForeignRoutes.delete">
                <button
                  type="button"
                  class="inline-flex items-center gap-2 rounded-full border border-border/60 bg-background px-4 py-2 text-xs font-semibold text-rose-600 transition hover:border-rose-300 hover:bg-rose-50 disabled:cursor-not-allowed disabled:opacity-60"
                  :disabled="manualForeignModal.saving"
                  @click.prevent="deleteManualForeignFromModal()"
                >
                  <i class="fa-solid fa-trash"></i>
                  Видалити зв'язок
                </button>
              </template>
              <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                <button
                  type="button"
                  class="inline-flex items-center gap-2 rounded-full border border-border/60 bg-background px-4 py-2 text-xs font-semibold text-muted-foreground transition hover:border-primary/40 hover:text-primary disabled:cursor-not-allowed disabled:opacity-60"
                  :disabled="manualForeignModal.saving"
                  @click.prevent="closeManualForeignModal()"
                >
                  Скасувати
                </button>
                <button
                  type="submit"
                  class="inline-flex items-center gap-2 rounded-full border border-primary/50 bg-primary/10 px-4 py-2 text-xs font-semibold text-primary transition hover:bg-primary/20 disabled:cursor-not-allowed disabled:opacity-60"
                  :disabled="manualForeignModal.saving"
                >
                  <span x-show="!manualForeignModal.saving">Зберегти</span>
                  <span x-show="manualForeignModal.saving" x-cloak>Збереження...</span>
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>

      <div
        x-show="valueModal.open"
        x-cloak
        class="fixed inset-0 z-50 flex justify-center px-4 overflow-y-auto"
        :class="valueModal.editing ? 'items-start pt-4 pb-6 sm:pt-10' : 'items-center py-6'"
        role="dialog"
        aria-modal="true"
        x-ref="valueModalOverlay"
        style="margin-top: 0px"
      >
        <div class="absolute inset-0 backdrop-blur-sm" @click="closeValueModal()"></div>
        <div class="relative z-10 w-full max-w-2xl rounded-3xl border border-border/70 bg-white p-6 shadow-xl">
          <div class="flex items-start justify-between gap-4">
            <div>
              <h2 class="text-lg font-semibold text-foreground">Повне значення</h2>
              <p class="mt-1 text-sm text-muted-foreground">
                Таблиця: <span class="font-medium text-foreground" x-text="valueModal.table || '—'"></span>,
                колонка: <span class="font-medium text-foreground" x-text="valueModal.column || '—'"></span>
              </p>
            </div>
            <div class="flex items-center gap-2">
              <button
                type="button"
                class="inline-flex items-center gap-2 rounded-full border border-border/60 bg-background px-3 py-1.5 text-sm font-medium text-muted-foreground transition hover:border-primary/60 hover:text-primary disabled:cursor-not-allowed disabled:opacity-60"
                x-show="!valueModal.loading && !valueModal.error && !valueModal.editing"
                x-cloak
                :disabled="valueModal.loading"
                @click="startEditingValue()"
              >
                <i class="fa-solid fa-pen"></i>
                Редагувати
              </button>
              <button
                type="button"
                class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-border/60 text-muted-foreground transition hover:text-foreground focus:outline-none focus:ring-2 focus:ring-primary/40"
                @click="closeValueModal()"
              >
                <i class="fa-solid fa-xmark"></i>
              </button>
            </div>
          </div>
          <div class="mt-4 space-y-3 text-sm text-foreground">
            <template x-if="valueModal.loading">
              <div class="rounded-2xl border border-dashed border-border/60 bg-muted/30 p-4 text-center text-sm text-muted-foreground">
                Завантаження значення...
              </div>
            </template>
            <template x-if="!valueModal.loading && valueModal.error">
              <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-600" x-text="valueModal.error"></div>
            </template>
            <template x-if="!valueModal.loading && !valueModal.error">
              <div class="space-y-3">
                <template x-if="valueModal.updateError">
                  <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-600" x-text="valueModal.updateError"></div>
                </template>
                <div class="rounded-2xl border border-border/60 bg-background p-4">
                  <template x-if="!valueModal.editing">
                    <pre class="max-h-96 whitespace-pre-wrap break-words text-[15px]" x-html="highlightText(valueModal.value, valueModal.searchTerm)"></pre>
                  </template>
                  <template
                    x-if="valueModal.editing && (!valueModal.foreignKey || !valueModal.foreignRecords.visible)"
                  >
                    <textarea
                      class="w-full min-h-[120px] resize-none overflow-hidden rounded-2xl border border-input bg-white px-3 py-2 text-[15px] font-mono text-foreground shadow-inner focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
                      x-model="valueModal.editValue"
                      :disabled="valueModal.saving"
                      x-ref="valueEditor"
                      x-init="autoResizeValueEditor($el)"
                      @input="autoResizeValueEditor($event.target)"
                    ></textarea>
                  </template>
                  <template x-if="valueModal.editing && valueModal.foreignKey">
                    <div class="mt-3 space-y-3 rounded-2xl border border-dashed border-primary/40 bg-primary/5 p-4">
                      <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div class="text-sm text-muted-foreground">
                          Це поле є зовнішнім ключем на таблицю
                          <span class="font-medium text-foreground" x-text="valueModal.foreignKey.table"></span>.
                          Колонка ключа:
                          <span class="font-medium text-foreground" x-text="valueModal.foreignKey.column"></span>
                          <template x-if="valueModal.foreignKey.displayColumn">
                            <span class="block text-xs text-muted-foreground">
                              Відображення за колонкою
                              <span class="font-medium text-foreground" x-text="valueModal.foreignKey.displayColumn"></span>
                            </span>
                          </template>
                        </div>
                        <button
                          type="button"
                          class="inline-flex items-center gap-2 rounded-full border border-primary/40 bg-primary/10 px-4 py-1.5 text-xs font-semibold text-primary transition hover:bg-primary/20 focus:outline-none focus:ring-2 focus:ring-primary/40"
                          @click="toggleForeignRecords()"
                        >
                          <i class="fa-solid" :class="valueModal.foreignRecords.visible ? 'fa-eye-slash' : 'fa-database'"></i>
                          <span x-text="valueModal.foreignRecords.visible ? 'Сховати записи' : 'Обрати запис'"></span>
                        </button>
                      </div>
                      <div x-show="valueModal.foreignRecords.visible" x-collapse class="space-y-3">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                          <div class="text-sm text-muted-foreground">
                            Пошук у пов'язаних записах
                          </div>
                          <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:items-center">
                            <div class="relative w-full sm:w-48">
                              <select
                                class="w-full appearance-none rounded-full border border-input bg-white px-4 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
                                x-model="valueModal.foreignRecords.searchColumn"
                                @change="updateForeignRecordsSearchColumn($event.target.value)"
                              >
                                <option value="">Усі поля</option>
                                <template x-for="columnName in valueModal.foreignRecords.columns" :key="columnName">
                                  <option :value="columnName" x-text="columnName"></option>
                                </template>
                              </select>
                              <span class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-xs text-muted-foreground">
                                <i class="fa-solid fa-chevron-down"></i>
                              </span>
                            </div>
                            <input
                              type="search"
                              class="w-full rounded-full border border-input bg-white px-4 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
                              placeholder="Пошук пов'язаних записів..."
                              x-model="valueModal.foreignRecords.query"
                              @input.debounce.500ms="searchForeignRecords()"
                            />
                            <button
                              type="button"
                              class="inline-flex items-center gap-2 rounded-full border border-border/60 bg-background px-4 py-2 text-sm font-medium text-muted-foreground transition hover:border-primary/60 hover:text-primary disabled:cursor-not-allowed disabled:opacity-60"
                              @click="searchForeignRecords()"
                              :disabled="valueModal.foreignRecords.loading"
                            >
                              <i class="fa-solid fa-magnifying-glass"></i>
                              Знайти
                            </button>
                          </div>
                        </div>
                        <div
                          x-show="valueModal.foreignRecords.loading"
                          class="rounded-2xl border border-dashed border-border/60 bg-muted/30 p-3 text-sm text-muted-foreground"
                        >
                          Завантаження пов'язаних записів...
                        </div>
                        <template x-if="valueModal.foreignRecords.error">
                          <div
                            class="rounded-2xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-600"
                            x-text="valueModal.foreignRecords.error"
                          ></div>
                        </template>
                        <template x-if="!valueModal.foreignRecords.loading && !valueModal.foreignRecords.error">
                          <div class="space-y-2">
                            <template x-if="Array.isArray(valueModal.foreignRecords.options) && valueModal.foreignRecords.options.length === 0">
                              <div class="rounded-2xl border border-dashed border-border/60 bg-muted/30 p-3 text-sm text-muted-foreground">
                                Записи не знайдено.
                              </div>
                            </template>
                            <template x-if="Array.isArray(valueModal.foreignRecords.options) && valueModal.foreignRecords.options.length > 0">
                              <div class="space-y-2 max-h-80 overflow-y-auto pr-1 sm:max-h-96">
                                <template x-for="record in valueModal.foreignRecords.options" :key="foreignRecordKey(record)">
                                  <div class="rounded-2xl border border-border/60 bg-white p-3 shadow-soft/10">
                                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                      <button
                                        type="button"
                                        class="w-full rounded-xl border px-4 py-2.5 text-left text-sm transition focus:outline-none focus:ring-2 focus:ring-primary/40 sm:flex-1"
                                        :class="isForeignRecordSelected(record)
                                          ? 'border-primary bg-primary/10 text-primary'
                                          : 'border-border/60 bg-white hover:border-primary/60 hover:text-primary'"
                                        @click="selectForeignRecord(record)"
                                      >
                                        <div class="font-semibold" x-html="highlightForeignRecordText(formatForeignRecordLabel(record))"></div>
                                        <div
                                          class="mt-1 text-xs text-muted-foreground"
                                          x-html="highlightForeignRecordText(formatForeignRecordSummary(record))"
                                        ></div>
                                      </button>
                                      <button
                                        type="button"
                                        class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-medium transition focus:outline-none focus:ring-2 focus:ring-primary/40 sm:self-start"
                                        :class="isForeignRecordPreviewed(record)
                                          ? 'border-primary/50 bg-primary/10 text-primary'
                                          : 'border-border/60 bg-background text-muted-foreground hover:border-primary/60 hover:text-primary'"
                                        @click.stop="previewForeignRecord(record)"
                                        :disabled="valueModal.foreignRecords.preview.loading && valueModal.foreignRecords.preview.key === foreignRecordKey(record)"
                                      >
                                        <i class="fa-solid fa-eye text-[10px]"></i>
                                        Переглянути
                                      </button>
                                    </div>
                                    <template x-if="isForeignRecordPreviewed(record)">
                                      <div class="mt-3 rounded-2xl border border-dashed border-border/60 bg-muted/20 p-3 text-sm">
                                        <template x-if="valueModal.foreignRecords.preview.loading">
                                          <div class="text-muted-foreground">Завантаження повного запису...</div>
                                        </template>
                                        <template x-if="!valueModal.foreignRecords.preview.loading && valueModal.foreignRecords.preview.error">
                                          <div class="text-rose-600" x-text="valueModal.foreignRecords.preview.error"></div>
                                        </template>
                                        <template x-if="!valueModal.foreignRecords.preview.loading && !valueModal.foreignRecords.preview.error">
                                          <dl class="grid grid-cols-1 gap-3">
                                            <template x-for="fieldName in valueModal.foreignRecords.preview.columns" :key="`${foreignRecordKey(record)}:${fieldName}`">
                                              <div class="space-y-1">
                                                <dt class="text-[13px] font-semibold uppercase tracking-wide text-muted-foreground/80" x-text="fieldName"></dt>
                                                <dd
                                                  class="rounded-xl border border-border/60 bg-white px-3 py-2 text-sm text-foreground"
                                                  x-html="highlightForeignRecordText(formatCell((valueModal.foreignRecords.preview.record || {})[fieldName]))"
                                                ></dd>
                                              </div>
                                            </template>
                                          </dl>
                                        </template>
                                      </div>
                                    </template>
                                  </div>
                                </template>
                              </div>
                            </template>
                          </div>
                        </template>
                        <div
                          class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
                          x-show="valueModal.foreignRecords.lastPage > 1"
                        >
                          <div class="flex items-center gap-2">
                            <button
                              type="button"
                              class="inline-flex items-center gap-2 rounded-full border border-border/60 bg-background px-3 py-1.5 text-xs font-medium text-muted-foreground transition hover:border-primary/60 hover:text-primary disabled:cursor-not-allowed disabled:opacity-60"
                              @click="changeForeignRecordsPage((valueModal.foreignRecords.page || 1) - 1)"
                              :disabled="valueModal.foreignRecords.loading || (valueModal.foreignRecords.page || 1) <= 1"
                            >
                              <i class="fa-solid fa-chevron-left text-[10px]"></i>
                              Попередня
                            </button>
                            <button
                              type="button"
                              class="inline-flex items-center gap-2 rounded-full border border-border/60 bg-background px-3 py-1.5 text-xs font-medium text-muted-foreground transition hover:border-primary/60 hover:text-primary disabled:cursor-not-allowed disabled:opacity-60"
                              @click="changeForeignRecordsPage((valueModal.foreignRecords.page || 1) + 1)"
                              :disabled="valueModal.foreignRecords.loading || (valueModal.foreignRecords.page || 1) >= (valueModal.foreignRecords.lastPage || 1)"
                            >
                              Наступна
                              <i class="fa-solid fa-chevron-right text-[10px]"></i>
                            </button>
                          </div>
                          <div class="text-xs text-muted-foreground">
                            Сторінка
                            <span class="font-medium text-foreground" x-text="valueModal.foreignRecords.page"></span>
                            з
                            <span class="font-medium text-foreground" x-text="valueModal.foreignRecords.lastPage"></span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </template>
                </div>
                <div class="flex items-center justify-end gap-3" x-show="valueModal.editing">
                  <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-full border border-border/60 bg-background px-4 py-2 text-sm font-medium text-muted-foreground transition hover:border-primary/60 hover:text-primary disabled:cursor-not-allowed disabled:opacity-60"
                    :disabled="valueModal.saving"
                    @click="cancelEditingValue()"
                  >
                    Скасувати
                  </button>
                  <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-full border border-emerald-600 bg-emerald-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 disabled:cursor-not-allowed disabled:opacity-60"
                    :disabled="valueModal.saving"
                    @click="saveEditedValue()"
                  >
                    <span x-show="!valueModal.saving">Зберегти</span>
                    <span x-show="valueModal.saving" x-cloak>Збереження...</span>
                  </button>
                </div>
              </div>
            </template>
          </div>
        </div>
      </div>
    </div>

    <div
      x-show="contentManagement.deletionModal.open"
      x-cloak
      class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6"
      role="dialog"
      aria-modal="true"
    >
      <div
        class="absolute inset-0 bg-background/80 backdrop-blur-sm"
        @click="contentManagement.deletionModal.loading ? null : closeContentManagementDeletionModal()"
      ></div>
      <div class="relative z-10 w-full max-w-md rounded-3xl border border-border/70 bg-card p-6 shadow-xl">
        <div class="flex items-start justify-between gap-4">
          <div>
            <h2 class="text-lg font-semibold text-foreground">Підтвердження видалення</h2>
            <p class="mt-1 text-sm text-muted-foreground">
              Таблиця
              <span class="font-semibold text-foreground" x-text="contentManagement.deletionModal.label || contentManagement.deletionModal.table"></span>
              буде прибрана з меню.
            </p>
          </div>
          <button
            type="button"
            class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-border/60 text-muted-foreground transition hover:text-foreground focus:outline-none focus:ring-2 focus:ring-primary/40"
            @click="contentManagement.deletionModal.loading ? null : closeContentManagementDeletionModal()"
          >
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>
        <div class="mt-4 space-y-3 text-sm text-muted-foreground">
          <p>
            Ви впевнені, що хочете видалити запис
            <span class="font-semibold text-foreground" x-text="contentManagement.deletionModal.label || contentManagement.deletionModal.table"></span>
            (<span class="font-mono text-xs text-muted-foreground/80" x-text="contentManagement.deletionModal.table"></span>)
            з меню?
          </p>
          <p class="text-xs text-muted-foreground">
            Дію не можна скасувати, проте ви зможете знову додати таблицю через налаштування.
          </p>
        </div>
        <template x-if="contentManagement.deletionModal.error">
          <div
            class="mt-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-600"
            x-text="contentManagement.deletionModal.error"
          ></div>
        </template>
        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end">
          <button
            type="button"
            class="inline-flex items-center justify-center gap-2 rounded-full border border-border/60 bg-background px-4 py-2 text-sm font-medium text-muted-foreground transition hover:border-primary/60 hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary/40 disabled:cursor-not-allowed disabled:opacity-60"
            :disabled="contentManagement.deletionModal.loading"
            @click="contentManagement.deletionModal.loading ? null : closeContentManagementDeletionModal()"
          >
            Скасувати
          </button>
          <button
            type="button"
            class="inline-flex items-center justify-center gap-2 rounded-full border border-rose-500 bg-rose-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-600 focus:outline-none focus:ring-2 focus:ring-rose-500/40 disabled:cursor-not-allowed disabled:opacity-60"
            :disabled="contentManagement.deletionModal.loading"
            @click="confirmContentManagementDeletion()"
          >
            <span x-show="!contentManagement.deletionModal.loading">Видалити</span>
            <span x-show="contentManagement.deletionModal.loading" x-cloak>Видалення...</span>
          </button>
        </div>
      </div>
    </div>

    <div
      x-show="activeTab === 'content-management'"
      x-cloak
      class="grid gap-6 lg:grid-cols-[280px_1fr]"
    >
      <aside class="space-y-4 rounded-3xl border border-border/70 bg-card/80 p-6 shadow-soft">
        <div class="flex flex-wrap items-start justify-between gap-3">
          <div class="space-y-1">
            <h2 class="text-lg font-semibold text-foreground">Меню таблиць</h2>
            <p class="text-xs text-muted-foreground">Виберіть таблицю або налаштуйте список.</p>
          </div>
          <button
            type="button"
            class="inline-flex items-center gap-2 rounded-full border border-border/60 bg-background px-3 py-1.5 text-xs font-semibold text-muted-foreground transition hover:border-primary/60 hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
            @click="toggleContentManagementMenuSettings()"
          >
            <i class="fa-solid fa-sliders text-[11px]"></i>
            Налаштувати
          </button>
        </div>

        <div
          x-show="contentManagement.menuSettings.open"
          x-transition
          x-cloak
          class="space-y-4 rounded-2xl border border-border/60 bg-background/70 p-4"
        >
          <div class="space-y-1">
            <label class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Таблиця</label>
            <select
              x-model="contentManagement.menuSettings.table"
              class="w-full rounded-xl border border-input bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
            >
              <option value="">Оберіть таблицю</option>
              <template x-for="name in contentManagementAvailableTables" :key="`cm-option-${name}`">
                <option :value="name" x-text="name"></option>
              </template>
            </select>
          </div>
          <div class="space-y-1">
            <label class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Назва в меню</label>
            <input
              type="text"
              class="w-full rounded-xl border border-input bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
              placeholder="Наприклад, &quot;Пости&quot;"
              x-model.trim="contentManagement.menuSettings.label"
            />
            <p class="text-[11px] text-muted-foreground">Якщо залишити порожнім, буде використано назву таблиці.</p>
          </div>
          <template x-if="contentManagement.menuSettings.error">
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-600" x-text="contentManagement.menuSettings.error"></div>
          </template>
          <div class="flex items-center justify-end gap-2">
            <button
              type="button"
              class="rounded-full border border-border/60 bg-background px-4 py-1.5 text-xs font-semibold text-muted-foreground transition hover:border-primary/60 hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary/40 disabled:cursor-not-allowed disabled:opacity-60"
              @click="closeContentManagementMenuSettings()"
              :disabled="contentManagement.menuSettings.saving"
            >
              Скасувати
            </button>
            <button
              type="button"
              class="inline-flex items-center gap-2 rounded-full border border-primary bg-primary px-4 py-1.5 text-xs font-semibold text-white transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/40 disabled:cursor-not-allowed disabled:opacity-60"
              @click="addContentManagementMenuItem()"
              :disabled="contentManagement.menuSettings.saving"
            >
              <span x-show="!contentManagement.menuSettings.saving">Додати</span>
              <span x-show="contentManagement.menuSettings.saving" x-cloak>Збереження...</span>
            </button>
          </div>
        </div>

        <div class="space-y-2">
          <template x-if="contentManagement.menu.length === 0">
            <div class="rounded-2xl border border-dashed border-border/60 bg-muted/20 p-4 text-sm text-muted-foreground">
              Меню порожнє. Додайте таблицю через налаштування.
            </div>
          </template>
          <template x-for="item in contentManagement.menu" :key="`cm-item-${item.table}`">
            <div class="flex flex-wrap items-center gap-2">
              <button
                type="button"
                class="flex-1 rounded-2xl border px-4 py-3 text-left transition focus:outline-none focus:ring-2 focus:ring-primary/40"
                :class="contentManagement.selectedTable === item.table ? 'border-primary/70 bg-primary/10 text-primary' : 'border-border/60 bg-background text-foreground hover:border-primary/60 hover:text-primary'"
                @click="selectContentManagementTable(item.table)"
              >
                <div class="font-semibold" x-text="item.label || item.table"></div>
                <div class="text-xs text-muted-foreground" x-text="item.table"></div>
              </button>
              <button
                type="button"
                class="inline-flex items-center justify-center rounded-full border border-border/60 bg-background p-2 text-muted-foreground transition hover:border-rose-300 hover:bg-rose-50 hover:text-rose-600 focus:outline-none focus:ring-2 focus:ring-rose-200/70"
                x-show="contentManagement.menuSettings.open"
                x-cloak
                @click="openContentManagementDeletionModal(item)"
                :disabled="contentManagement.deletionModal.loading && contentManagement.deletionModal.table === item.table"
                :aria-disabled="contentManagement.deletionModal.loading && contentManagement.deletionModal.table === item.table"
                aria-label="Видалити таблицю з меню"
              >
                <i class="fa-solid fa-trash-can text-xs"></i>
              </button>
            </div>
          </template>
        </div>
      </aside>
      <section class="space-y-4 rounded-3xl border border-border/70 bg-card/80 p-6 shadow-soft">
        <template x-if="!contentManagement.selectedTable">
          <div class="flex h-full min-h-[260px] flex-col items-center justify-center gap-3 text-center text-sm text-muted-foreground">
            <i class="fa-regular fa-folder-open text-3xl text-muted-foreground/80"></i>
            <span>Оберіть таблицю з меню ліворуч, щоб переглянути дані.</span>
          </div>
        </template>
        <template x-if="contentManagement.selectedTable">
          <div class="space-y-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
              <div>
                <h2 class="text-2xl font-semibold text-foreground" x-text="contentManagementLabel(contentManagement.selectedTable)"></h2>
                <p class="text-sm text-muted-foreground" x-text="contentManagement.selectedTable"></p>
              </div>
              <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-3">
                <label class="inline-flex items-center gap-2 text-xs font-semibold text-muted-foreground">
                  На сторінці:
                  <select
                    class="rounded-xl border border-input bg-background px-2 py-1 text-xs focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
                    x-model.number="contentManagement.viewer.perPage"
                    @change="changeContentManagementPerPage(contentManagement.viewer.perPage)"
                  >
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                  </select>
                </label>
                <button
                  type="button"
                  class="inline-flex items-center gap-2 rounded-full border border-border/60 bg-background px-4 py-1.5 text-xs font-semibold text-muted-foreground transition hover:border-primary/60 hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary/40 disabled:cursor-not-allowed disabled:opacity-60"
                  @click="refreshContentManagementTable()"
                  :disabled="contentManagement.viewer.loading"
                >
                  <i class="fa-solid fa-rotate-right text-[11px]"></i>
                  Оновити
                </button>
              </div>
            </div>

            <template x-if="contentManagement.viewer.error">
              <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-600" x-text="contentManagement.viewer.error"></div>
            </template>

            <template x-if="contentManagement.viewer.loading">
              <div class="rounded-2xl border border-dashed border-border/60 bg-muted/20 p-4 text-sm text-muted-foreground">Завантаження даних таблиці...</div>
            </template>

            <template x-if="!contentManagement.viewer.loading && !contentManagement.viewer.error && contentManagement.viewer.rows.length === 0">
              <div class="rounded-2xl border border-dashed border-border/60 bg-muted/20 p-4 text-sm text-muted-foreground">Записів не знайдено.</div>
            </template>

            <template x-if="!contentManagement.viewer.loading && !contentManagement.viewer.error && contentManagement.viewer.rows.length > 0">
              <div class="space-y-4">
                <div class="overflow-x-auto">
                  <table class="min-w-full divide-y divide-border/60 text-[15px]">
                    <thead class="text-left text-xs uppercase tracking-wider text-muted-foreground">
                      <tr>
                        <template x-for="column in contentManagement.viewer.columns" :key="`cm-column-${column}`">
                          <th class="px-3 py-2 font-medium" x-text="column"></th>
                        </template>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-border/60 text-[15px] text-foreground">
                      <template x-for="(row, rowIndex) in contentManagement.viewer.rows" :key="`cm-row-${rowIndex}`">
                        <tr class="hover:bg-muted/40 transition">
                          <template x-for="column in contentManagement.viewer.columns" :key="`cm-cell-${rowIndex}-${column}`">
                            <td class="px-3 py-2 align-top">
                              <div class="text-sm text-foreground" x-text="formatCell(row[column])"></div>
                            </td>
                          </template>
                        </tr>
                      </template>
                    </tbody>
                  </table>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                  <div class="text-xs text-muted-foreground">
                    Всього записів:
                    <span class="font-semibold text-foreground" x-text="contentManagement.viewer.total"></span>
                  </div>
                  <div class="flex items-center gap-2">
                    <button
                      type="button"
                      class="inline-flex items-center gap-2 rounded-full border border-border/60 bg-background px-3 py-1.5 text-xs font-medium text-muted-foreground transition hover:border-primary/60 hover:text-primary disabled:cursor-not-allowed disabled:opacity-60"
                      @click="changeContentManagementPage(contentManagement.viewer.page - 1)"
                      :disabled="contentManagement.viewer.loading || contentManagement.viewer.page <= 1"
                    >
                      <i class="fa-solid fa-chevron-left text-[10px]"></i>
                      Попередня
                    </button>
                    <div class="text-xs text-muted-foreground">
                      <span class="font-semibold text-foreground" x-text="contentManagement.viewer.page"></span>
                      з
                      <span class="font-semibold text-foreground" x-text="contentManagement.viewer.lastPage"></span>
                    </div>
                    <button
                      type="button"
                      class="inline-flex items-center gap-2 rounded-full border border-border/60 bg-background px-3 py-1.5 text-xs font-medium text-muted-foreground transition hover:border-primary/60 hover:text-primary disabled:cursor-not-allowed disabled:opacity-60"
                      @click="changeContentManagementPage(contentManagement.viewer.page + 1)"
                      :disabled="contentManagement.viewer.loading || contentManagement.viewer.page >= contentManagement.viewer.lastPage"
                    >
                      Наступна
                      <i class="fa-solid fa-chevron-right text-[10px]"></i>
                    </button>
                  </div>
                </div>
              </div>
            </template>
          </div>
        </template>
      </section>
    </div>
  </div>
@endsection

@push('head-scripts')
  @once
    <script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
  @endonce
  <script>
    window.databaseStructureViewer = function (
      tables,
      recordsRoute,
      deleteRoute,
      valueRoute,
      recordRoute,
      updateRoute,
      structureRoute,
      manualForeignStoreRoute,
      manualForeignDeleteRoute,
      contentManagementMenu,
      contentManagementRoutes,
      viewOptions = {},
    ) {
      const extractTables = (payload) => {
          if (Array.isArray(payload)) {
            return payload.filter(Boolean);
          }

          if (payload && typeof payload === 'object') {
            if (Array.isArray(payload.tables)) {
              return payload.tables.filter(Boolean);
            }

            if (Array.isArray(payload.data)) {
              return payload.data.filter(Boolean);
            }

            return Object.values(payload).filter((item) => {
              if (!item || typeof item !== 'object' || Array.isArray(item)) {
                return false;
              }

              const keys = Object.keys(item);

              return keys.some((key) => ['name', 'columns', 'comment', 'engine'].includes(key));
            });
          }

          return [];
        };

        const normalizeNullable = (value) => {
          if (value === true || value === false) {
            return value;
          }

          if (typeof value === 'string') {
            const normalized = value.toLowerCase();
            return ['1', 'true', 'yes', 'y', 't'].includes(normalized);
          }

          if (typeof value === 'number') {
            return value === 1;
          }

          return Boolean(value);
        };

        const normalizeForeignDefinition = (rawForeign) => {
          if (!rawForeign || typeof rawForeign !== 'object' || Array.isArray(rawForeign)) {
            return null;
          }

          const foreignTable = typeof rawForeign.table === 'string' ? rawForeign.table.trim() : '';
          const foreignColumn = typeof rawForeign.column === 'string' ? rawForeign.column.trim() : '';

          if (!foreignTable || !foreignColumn) {
            return null;
          }

          const constraint = typeof rawForeign.constraint === 'string' && rawForeign.constraint.trim() !== ''
            ? rawForeign.constraint.trim()
            : null;

          let displayColumn = null;

          if (typeof rawForeign.display_column === 'string' && rawForeign.display_column.trim() !== '') {
            displayColumn = rawForeign.display_column.trim();
          } else if (typeof rawForeign.displayColumn === 'string' && rawForeign.displayColumn.trim() !== '') {
            displayColumn = rawForeign.displayColumn.trim();
          }

          const rawLabels = Array.isArray(rawForeign.label_columns)
            ? rawForeign.label_columns
            : (Array.isArray(rawForeign.labelColumns) ? rawForeign.labelColumns : []);

          const labelColumns = rawLabels
            .map((label) => (typeof label === 'string' ? label.trim() : ''))
            .filter((label) => label !== '');

          const manual = Boolean(rawForeign.manual);

          return {
            table: foreignTable,
            column: foreignColumn,
            constraint,
            displayColumn,
            labelColumns,
            manual,
          };
        };

        const normalizeColumns = (rawColumns) => {
          if (!Array.isArray(rawColumns)) {
            return [];
          }

          return rawColumns
            .map((column) => {
              if (column && typeof column === 'object' && !Array.isArray(column)) {
                const columnName = typeof column.name === 'string' ? column.name.trim() : '';

                if (!columnName) {
                  return null;
                }

                const normalizedForeign = normalizeForeignDefinition(column.foreign);

                return {
                  name: columnName,
                  type: typeof column.type === 'string' ? column.type.trim() : '',
                  nullable: normalizeNullable(column.nullable),
                  default: Object.prototype.hasOwnProperty.call(column, 'default') ? column.default : null,
                  key: typeof column.key === 'string' && column.key.trim() !== '' ? column.key.trim() : null,
                  extra: typeof column.extra === 'string' && column.extra.trim() !== '' ? column.extra.trim() : null,
                  comment: typeof column.comment === 'string' && column.comment.trim() !== '' ? column.comment.trim() : null,
                  foreign: normalizedForeign,
                };
              }

              if (typeof column === 'string' && column.trim().length > 0) {
                return {
                  name: column.trim(),
                  type: '',
                  nullable: true,
                  default: null,
                  key: null,
                  extra: null,
                  comment: null,
                  foreign: null,
                };
              }

              return null;
            })
            .filter(Boolean);
        };

        const normalizeContentManagementMenuItem = (item) => {
          if (typeof item === 'string') {
            const tableName = item.trim();

            if (!tableName) {
              return null;
            }

            return {
              table: tableName,
              label: tableName,
            };
          }

          if (!item || typeof item !== 'object') {
            return null;
          }

          const tableName = typeof item.table === 'string' ? item.table.trim() : '';

          if (!tableName) {
            return null;
          }

          const label = typeof item.label === 'string' && item.label.trim() !== ''
            ? item.label.trim()
            : tableName;

          return {
            table: tableName,
            label,
          };
        };

        const normalizeContentManagementMenu = (rawMenu) => {
          if (!Array.isArray(rawMenu)) {
            return [];
          }

          const seen = new Set();

          return rawMenu
            .map((item) => normalizeContentManagementMenuItem(item))
            .filter((item) => {
              if (!item) {
                return false;
              }

              if (seen.has(item.table)) {
                return false;
              }

              seen.add(item.table);

              return true;
            });
        };

        const normalizeContentManagementRoutes = (routes) => {
          const normalized = routes && typeof routes === 'object' ? routes : {};

          return {
            menuStore: typeof normalized.menuStore === 'string' ? normalized.menuStore : '',
            menuDelete: typeof normalized.menuDelete === 'string' ? normalized.menuDelete : '',
          };
        };

        const createContentManagementViewerState = () => ({
          table: '',
          loading: false,
          error: null,
          columns: [],
          rows: [],
          page: 1,
          perPage: 20,
          total: 0,
          lastPage: 1,
          requestId: 0,
          loaded: false,
        });

        const createForeignRecordPreviewState = () => ({
          key: '',
          visible: false,
          loading: false,
          error: null,
          record: null,
          columns: [],
          requestId: 0,
        });

        const createForeignRecordsState = () => ({
          visible: false,
          loading: false,
          loaded: false,
          error: null,
          options: [],
          columns: [],
          page: 1,
          lastPage: 1,
          perPage: 8,
          query: '',
          searchColumn: '',
          requestId: 0,
          selectedValue: '',
          preview: createForeignRecordPreviewState(),
        });

        const normalizedTables = extractTables(tables)
          .map((table) => {
            const tableObject = table && typeof table === 'object' && !Array.isArray(table)
              ? table
              : {};

            const fallbackName = typeof table === 'string' ? table : '';
            const name = typeof tableObject.name === 'string' && tableObject.name.trim().length > 0
              ? tableObject.name
              : fallbackName.trim();

            if (!name) {
              return null;
            }

            const normalizedColumns = normalizeColumns(tableObject.columns);
            const columnNames = normalizedColumns
              .map((column) => (typeof column.name === 'string' ? column.name : ''))
              .filter((column) => column.length > 0);

            let columnsCount = normalizedColumns.length;
            if (Object.prototype.hasOwnProperty.call(tableObject, 'columns_count')) {
              const numeric = Number(tableObject.columns_count);
              if (!Number.isNaN(numeric) && numeric >= 0) {
                columnsCount = Math.max(columnsCount, Math.trunc(numeric));
              }
            }

            const comment = typeof tableObject.comment === 'string' && tableObject.comment !== ''
              ? tableObject.comment
              : null;
            const engine = typeof tableObject.engine === 'string' && tableObject.engine !== ''
              ? tableObject.engine
              : null;

            const structureLoaded = normalizedColumns.length > 0;

            return {
              name,
              comment,
              engine,
              columnsCount,
              structure: {
                loading: false,
                loaded: structureLoaded,
                columns: normalizedColumns,
                error: null,
              },
              open: false,
              structureVisible: true,
              primaryKeys: structureLoaded
                ? normalizedColumns
                  .filter((column) => column && column.key === 'PRI' && column.name)
                  .map((column) => column.name)
                : [],
              records: {
                visible: false,
                loading: false,
                loaded: false,
                rows: [],
                columns: columnNames,
                error: null,
                page: 1,
                perPage: 20,
                total: 0,
                lastPage: 1,
                sort: null,
                direction: 'asc',
                filters: [],
                deletingRowIndex: null,
                search: '',
                searchInput: '',
                searchColumn: '',
                requestId: 0,
              },
            };
          })
          .filter(Boolean);

        const normalizedContentManagementMenu = normalizeContentManagementMenu(contentManagementMenu);
        const normalizedContentManagementRoutes = normalizeContentManagementRoutes(contentManagementRoutes);
        const normalizedViewOptions =
          viewOptions && typeof viewOptions === 'object' && !Array.isArray(viewOptions)
            ? viewOptions
            : {};
        const initialTab = normalizedViewOptions.initialTab === 'content-management'
          ? 'content-management'
          : 'structure';
        const standaloneTab = normalizedViewOptions.standaloneTab === 'content-management'
          ? 'content-management'
          : (normalizedViewOptions.standaloneTab === 'structure' ? 'structure' : '');
        const tabRoutesSource =
          normalizedViewOptions.tabRoutes && typeof normalizedViewOptions.tabRoutes === 'object'
            ? normalizedViewOptions.tabRoutes
            : {};
        const normalizedTabRoutes = {
          structure: typeof tabRoutesSource.structure === 'string' ? tabRoutesSource.structure : '',
          'content-management': typeof tabRoutesSource['content-management'] === 'string'
            ? tabRoutesSource['content-management']
            : '',
        };

        return {
          activeTab: standaloneTab || initialTab,
          standaloneTab,
          tabRoutes: normalizedTabRoutes,
          query: '',
          recordsRoute,
          recordsDeleteRoute: deleteRoute,
          recordsValueRoute: valueRoute,
          recordsShowRoute: recordRoute,
          recordsUpdateRoute: updateRoute,
          structureRoute,
          manualForeignRoutes: {
            store: typeof manualForeignStoreRoute === 'string' ? manualForeignStoreRoute : '',
            delete: typeof manualForeignDeleteRoute === 'string' ? manualForeignDeleteRoute : '',
          },
          contentManagementRoutes: normalizedContentManagementRoutes,
          contentManagement: {
            menu: normalizedContentManagementMenu,
            menuSettings: {
              open: false,
              table: '',
              label: '',
              saving: false,
              error: null,
            },
            selectedTable: '',
            viewer: createContentManagementViewerState(),
            deletionModal: {
              open: false,
              table: '',
              label: '',
              loading: false,
              error: null,
            },
          },
          csrfToken:
            document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ??
            (window.Laravel ? window.Laravel.csrfToken : ''),
          cellPreviewLimit: 120,
          manualForeignModal: {
            open: false,
            sourceTable: '',
            sourceColumn: '',
            targetTable: '',
            targetColumn: '',
            displayColumn: '',
            targetColumns: [],
            saving: false,
            error: null,
          },
          manualForeignErrors: {},
          bodyScrollLocked: false,
          bodyOriginalOverflow: '',
          bodyOriginalPaddingRight: '',
          valueModal: {
            open: false,
            table: '',
            column: '',
            value: '',
            rawValue: null,
            loading: false,
            error: null,
            searchTerm: '',
            editing: false,
            editValue: '',
            saving: false,
            updateError: null,
            identifiers: [],
            foreignKey: null,
            foreignRecords: createForeignRecordsState(),
          },
          filterOperators: [
            { value: '=', label: 'Дорівнює (=)' },
            { value: '!=', label: 'Не дорівнює (!=)' },
            { value: '<', label: 'Менше (<)' },
            { value: '<=', label: 'Менше або дорівнює (<=)' },
            { value: '>', label: 'Більше (>)' },
            { value: '>=', label: 'Більше або дорівнює (>=)' },
            { value: 'like', label: 'Містить (LIKE)' },
            { value: 'not like', label: 'Не містить (NOT LIKE)' },
          ],
          tables: normalizedTables,
          init() {
            this.syncBodyScrollLock();

            this.$watch('valueModal.open', () => {
              this.syncBodyScrollLock();
            });

            this.$watch('manualForeignModal.open', () => {
              this.syncBodyScrollLock();
            });

            this.$watch('contentManagement.deletionModal.open', () => {
              this.syncBodyScrollLock();
            });

            this.$watch('contentManagement.menuSettings.open', (open) => {
              if (open && !this.contentManagement.menuSettings.table) {
                const available = this.contentManagementAvailableTables;

                if (Array.isArray(available) && available.length > 0) {
                  [this.contentManagement.menuSettings.table] = available;
                }
              }
            });

            this.$watch('contentManagement.menu', (menu) => {
              if (
                this.contentManagement.selectedTable &&
                (!Array.isArray(menu) ||
                  !menu.some((item) => item && item.table === this.contentManagement.selectedTable))
              ) {
                this.contentManagement.selectedTable = '';
                this.resetContentManagementViewer();
              }

              if (
                this.activeTab === 'content-management' &&
                !this.contentManagement.selectedTable &&
                Array.isArray(this.contentManagement.menu) &&
                this.contentManagement.menu.length > 0
              ) {
                const firstItem = this.contentManagement.menu[0];

                if (firstItem && firstItem.table) {
                  this.selectContentManagementTable(firstItem.table);
                }
              }
            });

            if (
              this.activeTab === 'content-management' &&
              !this.contentManagement.selectedTable &&
              Array.isArray(this.contentManagement.menu) &&
              this.contentManagement.menu.length > 0
            ) {
              const firstItem = this.contentManagement.menu[0];

              if (firstItem && firstItem.table) {
                this.selectContentManagementTable(firstItem.table);
              }
            }
          },
          get filteredTables() {
            if (!this.query) {
              return this.tables;
            }

            const q = this.query.toLowerCase();
            return this.tables.filter((table) => {
              const tableName = typeof table.name === 'string' ? table.name : '';

              if (tableName.toLowerCase().includes(q)) {
                return true;
              }

              const columns = Array.isArray(table.structure?.columns) ? table.structure.columns : [];

              return columns.some((column) => {
                if (!column) {
                  return false;
                }

                const columnName = typeof column.name === 'string' ? column.name : '';
                const columnType = typeof column.type === 'string' ? column.type : '';

                return (
                  columnName.toLowerCase().includes(q) ||
                  columnType.toLowerCase().includes(q)
                );
              });
            });
          },
          get contentManagementAvailableTables() {
            const usedTables = new Set(
              Array.isArray(this.contentManagement.menu)
                ? this.contentManagement.menu
                  .map((item) => (item && typeof item.table === 'string' ? item.table : ''))
                  .filter((table) => table !== '')
                : [],
            );

            const available = this.tables
              .map((table) => (table && typeof table.name === 'string' ? table.name : ''))
              .filter((name) => name !== '' && !usedTables.has(name));

            return available.sort((a, b) => a.localeCompare(b));
          },
          setActiveTab(tab) {
            const normalized = typeof tab === 'string' ? tab.trim() : '';

            if (this.standaloneTab) {
              if (normalized && normalized !== this.standaloneTab) {
                const target = this.tabRoutes?.[normalized];

                if (typeof target === 'string' && target) {
                  window.location.href = target;
                }
              }

              return;
            }

            if (normalized === 'content-management') {
              this.activeTab = 'content-management';

              if (!this.contentManagement.selectedTable && this.contentManagement.menu.length > 0) {
                const firstItem = this.contentManagement.menu[0];

                if (firstItem && firstItem.table) {
                  this.selectContentManagementTable(firstItem.table);
                }
              }

              return;
            }

            this.activeTab = 'structure';
          },
          openContentManagementDeletionModal(item) {
            if (!item || typeof item.table !== 'string') {
              return;
            }

            const tableName = item.table.trim();

            if (!tableName) {
              return;
            }

            const label =
              typeof item.label === 'string' && item.label.trim() !== ''
                ? item.label.trim()
                : tableName;

            this.contentManagement.deletionModal.table = tableName;
            this.contentManagement.deletionModal.label = label;
            this.contentManagement.deletionModal.error = null;
            this.contentManagement.deletionModal.loading = false;
            this.contentManagement.deletionModal.open = true;
          },
          closeContentManagementDeletionModal() {
            if (this.contentManagement.deletionModal.loading) {
              return;
            }

            this.resetContentManagementDeletionModal();
          },
          resetContentManagementDeletionModal() {
            this.contentManagement.deletionModal.open = false;
            this.contentManagement.deletionModal.table = '';
            this.contentManagement.deletionModal.label = '';
            this.contentManagement.deletionModal.error = null;
            this.contentManagement.deletionModal.loading = false;
          },
          async confirmContentManagementDeletion() {
            if (this.contentManagement.deletionModal.loading) {
              return;
            }

            const tableName = typeof this.contentManagement.deletionModal.table === 'string'
              ? this.contentManagement.deletionModal.table.trim()
              : '';

            if (!tableName) {
              this.contentManagement.deletionModal.error = 'Не вдалося визначити таблицю для видалення.';
              return;
            }

            this.contentManagement.deletionModal.loading = true;
            this.contentManagement.deletionModal.error = null;

            try {
              await this.removeContentManagementMenuItem(tableName);
              this.resetContentManagementDeletionModal();
            } catch (error) {
              const message = error?.message ?? 'Сталася помилка під час видалення таблиці з меню.';
              this.contentManagement.deletionModal.error = message;
              this.contentManagement.menuSettings.error = message;
              this.contentManagement.menuSettings.open = true;
            } finally {
              this.contentManagement.deletionModal.loading = false;
            }
          },
          toggleContentManagementMenuSettings() {
            if (this.contentManagement.menuSettings.open) {
              this.closeContentManagementMenuSettings();
              return;
            }

            this.contentManagement.menuSettings.open = true;
            this.contentManagement.menuSettings.error = null;

            if (!this.contentManagement.menuSettings.table) {
              const available = this.contentManagementAvailableTables;

              if (Array.isArray(available) && available.length > 0) {
                [this.contentManagement.menuSettings.table] = available;
              }
            }
          },
          closeContentManagementMenuSettings() {
            this.contentManagement.menuSettings.open = false;
            this.resetContentManagementMenuSettings();
          },
          resetContentManagementMenuSettings() {
            this.contentManagement.menuSettings.table = '';
            this.contentManagement.menuSettings.label = '';
            this.contentManagement.menuSettings.error = null;
            this.contentManagement.menuSettings.saving = false;
          },
          async addContentManagementMenuItem() {
            if (!this.contentManagementRoutes.menuStore) {
              this.contentManagement.menuSettings.error = 'Маршрут збереження меню не налаштовано.';
              return;
            }

            const tableName = typeof this.contentManagement.menuSettings.table === 'string'
              ? this.contentManagement.menuSettings.table.trim()
              : '';

            if (!tableName) {
              this.contentManagement.menuSettings.error = 'Оберіть таблицю для додавання.';
              return;
            }

            const labelValue = typeof this.contentManagement.menuSettings.label === 'string'
              ? this.contentManagement.menuSettings.label.trim()
              : '';

            this.contentManagement.menuSettings.saving = true;
            this.contentManagement.menuSettings.error = null;

            try {
              const response = await fetch(this.contentManagementRoutes.menuStore, {
                method: 'POST',
                headers: {
                  Accept: 'application/json',
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': this.csrfToken || '',
                },
                body: JSON.stringify({
                  table: tableName,
                  label: labelValue,
                }),
              });

              if (!response.ok) {
                const payload = await response.json().catch(() => null);
                const message = payload?.message || 'Не вдалося зберегти таблицю для меню.';
                throw new Error(message);
              }

              const payload = await response.json();
              const normalizedItem = normalizeContentManagementMenuItem(payload);

              if (!normalizedItem) {
                throw new Error('Отримано некоректну відповідь від сервера.');
              }

              const updatedMenu = this.contentManagement.menu
                .filter((item) => item && item.table !== normalizedItem.table)
                .concat([normalizedItem])
                .sort((a, b) => a.label.localeCompare(b.label));

              this.contentManagement.menu = updatedMenu;
              this.closeContentManagementMenuSettings();
              await this.selectContentManagementTable(normalizedItem.table);
            } catch (error) {
              this.contentManagement.menuSettings.error = error?.message ?? 'Сталася помилка під час збереження меню.';
            } finally {
              this.contentManagement.menuSettings.saving = false;
            }
          },
          async removeContentManagementMenuItem(tableName) {
            const normalized = typeof tableName === 'string' ? tableName.trim() : '';

            if (!normalized) {
              throw new Error('Не вдалося визначити таблицю для видалення з меню.');
            }

            if (!this.contentManagementRoutes.menuDelete) {
              throw new Error('Маршрут видалення таблиці з меню не налаштовано.');
            }

            try {
              const url = new URL(
                this.contentManagementRoutes.menuDelete.replace('__TABLE__', encodeURIComponent(normalized)),
                window.location.origin,
              );

              const response = await fetch(url.toString(), {
                method: 'DELETE',
                headers: {
                  Accept: 'application/json',
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': this.csrfToken || '',
                },
              });

              if (!response.ok) {
                const payload = await response.json().catch(() => null);
                const message = payload?.message || 'Не вдалося видалити таблицю з меню.';
                throw new Error(message);
              }

              this.contentManagement.menu = this.contentManagement.menu.filter(
                (item) => item && item.table !== normalized,
              );
              this.contentManagement.menuSettings.error = null;

              if (this.contentManagement.selectedTable === normalized) {
                this.contentManagement.selectedTable = '';
                this.resetContentManagementViewer();

                if (this.activeTab === 'content-management' && this.contentManagement.menu.length > 0) {
                  const nextItem = this.contentManagement.menu[0];

                  if (nextItem && nextItem.table) {
                    await this.selectContentManagementTable(nextItem.table);
                  }
                }
              }

              return true;
            } catch (error) {
              throw error instanceof Error
                ? error
                : new Error(error?.message ?? 'Сталася помилка під час видалення таблиці з меню.');
            }
          },
          contentManagementLabel(tableName) {
            const normalized = typeof tableName === 'string' ? tableName.trim() : '';

            if (!normalized) {
              return '';
            }

            const item = this.contentManagement.menu.find(
              (entry) => entry && entry.table === normalized,
            );

            if (item && typeof item.label === 'string' && item.label.trim() !== '') {
              return item.label.trim();
            }

            return normalized;
          },
          resetContentManagementViewer() {
            const fresh = createContentManagementViewerState();
            const perPage = Number(this.contentManagement.viewer?.perPage) || fresh.perPage;

            this.contentManagement.viewer.table = fresh.table;
            this.contentManagement.viewer.loading = fresh.loading;
            this.contentManagement.viewer.error = fresh.error;
            this.contentManagement.viewer.columns = fresh.columns;
            this.contentManagement.viewer.rows = fresh.rows;
            this.contentManagement.viewer.page = 1;
            this.contentManagement.viewer.perPage = perPage;
            this.contentManagement.viewer.total = fresh.total;
            this.contentManagement.viewer.lastPage = fresh.lastPage;
            this.contentManagement.viewer.requestId = fresh.requestId;
            this.contentManagement.viewer.loaded = fresh.loaded;
          },
          async selectContentManagementTable(tableName) {
            const normalized = typeof tableName === 'string' ? tableName.trim() : '';

            if (!normalized) {
              return;
            }

            if (this.contentManagement.selectedTable === normalized && this.contentManagement.viewer.loaded) {
              return;
            }

            this.contentManagement.selectedTable = normalized;
            this.contentManagement.viewer.page = 1;
            this.contentManagement.viewer.error = null;
            this.contentManagement.viewer.rows = [];
            this.contentManagement.viewer.columns = [];

            await this.loadContentManagementTable(normalized);
          },
          async refreshContentManagementTable() {
            if (!this.contentManagement.selectedTable) {
              return;
            }

            await this.loadContentManagementTable(this.contentManagement.selectedTable);
          },
          async changeContentManagementPage(page) {
            if (!this.contentManagement.selectedTable || this.contentManagement.viewer.loading) {
              return;
            }

            const target = Number(page);
            const viewer = this.contentManagement.viewer;
            const last = viewer.lastPage || 1;
            const normalized = Number.isFinite(target) ? Math.min(Math.max(Math.trunc(target), 1), last) : 1;

            if (normalized === viewer.page) {
              return;
            }

            viewer.page = normalized;

            await this.loadContentManagementTable(this.contentManagement.selectedTable);
          },
          async changeContentManagementPerPage(perPage) {
            if (!this.contentManagement.selectedTable) {
              return;
            }

            const numeric = Number(perPage);

            if (!Number.isFinite(numeric) || numeric <= 0) {
              return;
            }

            if (this.contentManagement.viewer.perPage === numeric) {
              return;
            }

            this.contentManagement.viewer.perPage = Math.trunc(numeric);
            this.contentManagement.viewer.page = 1;

            await this.loadContentManagementTable(this.contentManagement.selectedTable);
          },
          async loadContentManagementTable(tableName) {
            const normalized = typeof tableName === 'string' ? tableName.trim() : '';

            if (!normalized) {
              return;
            }

            const viewer = this.contentManagement.viewer;
            const requestId = (viewer.requestId ?? 0) + 1;
            viewer.requestId = requestId;
            viewer.loading = true;
            viewer.error = null;
            viewer.loaded = false;

            try {
              const table = await this.ensureStructureLoadedByName(normalized);
              const structureColumns = table ? this.getTableColumnNames(table) : [];

              const url = new URL(
                this.recordsRoute.replace('__TABLE__', encodeURIComponent(normalized)),
                window.location.origin,
              );

              const currentPage = Number.isFinite(viewer.page) ? viewer.page : 1;
              const currentPerPage = Number.isFinite(viewer.perPage) ? viewer.perPage : 20;

              url.searchParams.set('page', currentPage);
              url.searchParams.set('per_page', currentPerPage);

              const response = await fetch(url.toString(), {
                headers: {
                  Accept: 'application/json',
                },
              });

              if (!response.ok) {
                const payload = await response.json().catch(() => null);
                const message = payload?.message || 'Не вдалося завантажити дані таблиці.';
                throw new Error(message);
              }

              const data = await response.json();

              if (viewer.requestId !== requestId) {
                return;
              }

              const rawColumns = Array.isArray(data.columns) ? data.columns : [];
              const normalizedColumns = rawColumns
                .map((column) => (typeof column === 'string' ? column.trim() : ''))
                .filter((column) => column !== '');

              const rows = Array.isArray(data.rows) ? data.rows : [];

              viewer.columns = normalizedColumns.length > 0
                ? normalizedColumns
                : (structureColumns.length > 0
                  ? structureColumns
                  : (rows.length > 0 ? Object.keys(rows[0]) : []));
              viewer.rows = rows.map((row) => (row && typeof row === 'object' ? row : {}));
              viewer.page = Number.isFinite(data.page) ? Number(data.page) : currentPage;
              viewer.perPage = Number.isFinite(data.per_page) ? Number(data.per_page) : currentPerPage;
              viewer.total = Number.isFinite(data.total) ? Number(data.total) : rows.length;
              viewer.lastPage = Number.isFinite(data.last_page)
                ? Math.max(1, Number(data.last_page))
                : Math.max(1, Math.ceil((viewer.total || 0) / (viewer.perPage || 1)));
              viewer.table = normalized;
              viewer.loaded = true;
            } catch (error) {
              if (viewer.requestId !== requestId) {
                return;
              }

              viewer.error = error?.message ?? 'Сталася помилка під час завантаження даних таблиці.';
              viewer.rows = [];
              viewer.columns = [];
              viewer.loaded = false;
            } finally {
              if (viewer.requestId === requestId) {
                viewer.loading = false;
              }
            }
          },
        syncBodyScrollLock() {
          const shouldLock =
            this.valueModal.open ||
            this.manualForeignModal.open ||
            this.contentManagement.deletionModal.open;
          this.toggleBodyScroll(shouldLock);
        },
        toggleBodyScroll(shouldLock) {
          if (typeof document === 'undefined') {
            return;
          }

          const body = document.body;

          if (!body) {
            return;
          }

          if (shouldLock) {
            if (this.bodyScrollLocked) {
              return;
            }

            this.bodyScrollLocked = true;
            this.bodyOriginalOverflow = body.style.overflow || '';
            this.bodyOriginalPaddingRight = body.style.paddingRight || '';

            if (typeof window !== 'undefined' && document.documentElement) {
              const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;

              if (scrollbarWidth > 0) {
                body.style.paddingRight = `${scrollbarWidth}px`;
              }
            }

            body.style.overflow = 'hidden';

            return;
          }

          if (!this.bodyScrollLocked) {
            return;
          }

          this.bodyScrollLocked = false;
          body.style.overflow = this.bodyOriginalOverflow;
          body.style.paddingRight = this.bodyOriginalPaddingRight;
        },
        handleEscape() {
          if (this.contentManagement.deletionModal.open) {
            this.closeContentManagementDeletionModal();
            return;
          }

          if (this.valueModal.open) {
            this.closeValueModal();
            return;
          }

          if (this.manualForeignModal.open) {
            this.closeManualForeignModal();
            return;
          }

          if (this.contentManagement.menuSettings.open) {
            this.closeContentManagementMenuSettings();
          }
        },
        findTableByName(name) {
          if (typeof name !== 'string') {
            return null;
          }

          const normalized = name.trim();

          if (!normalized) {
            return null;
          }

          return this.tables.find((table) => table && table.name === normalized) ?? null;
        },
        getTableColumnNames(table) {
          if (!table || !table.structure || !Array.isArray(table.structure.columns)) {
            return [];
          }

          return table.structure.columns
            .map((column) => (column && typeof column.name === 'string' ? column.name : null))
            .filter((name) => typeof name === 'string' && name !== '');
        },
        hasManualForeign(tableName, columnName) {
          const table = this.findTableByName(tableName);

          if (!table || !table.structure || !Array.isArray(table.structure.columns)) {
            return false;
          }

          const column = table.structure.columns.find((item) => item && item.name === columnName);

          return Boolean(column && column.foreign && column.foreign.manual);
        },
        async ensureStructureLoadedByName(name) {
          const table = this.findTableByName(name);

          if (!table) {
            return null;
          }

          await this.ensureStructureLoaded(table);

          return table;
        },
        setManualForeignError(tableName, columnName, message) {
          if (!this.manualForeignErrors || typeof this.manualForeignErrors !== 'object') {
            this.manualForeignErrors = {};
          }

          const normalizedTable = typeof tableName === 'string' ? tableName.trim() : '';
          const normalizedColumn = typeof columnName === 'string' ? columnName.trim() : '';

          if (!normalizedTable || !normalizedColumn) {
            return;
          }

          const key = `${normalizedTable}:${normalizedColumn}`;

          if (typeof message === 'string' && message.trim() !== '') {
            this.manualForeignErrors[key] = message.trim();
          } else {
            delete this.manualForeignErrors[key];
          }
        },
        getManualForeignError(tableName, columnName) {
          if (!this.manualForeignErrors) {
            return '';
          }

          const normalizedTable = typeof tableName === 'string' ? tableName.trim() : '';
          const normalizedColumn = typeof columnName === 'string' ? columnName.trim() : '';

          if (!normalizedTable || !normalizedColumn) {
            return '';
          }

          const key = `${normalizedTable}:${normalizedColumn}`;
          const message = this.manualForeignErrors[key];

          return typeof message === 'string' ? message : '';
        },
        getManualForeignRoute(template, tableName, columnName) {
          if (typeof template !== 'string' || template === '') {
            return '';
          }

          const tableValue = typeof tableName === 'string' ? encodeURIComponent(tableName) : '';
          const columnValue = typeof columnName === 'string' ? encodeURIComponent(columnName) : '';

          return template
            .split('__TABLE__').join(tableValue)
            .split('__COLUMN__').join(columnValue);
        },
        updateColumnForeign(table, columnName, foreign) {
          if (!table || !table.structure) {
            return;
          }

          const normalizedColumn = typeof columnName === 'string' ? columnName.trim() : '';

          if (!normalizedColumn) {
            return;
          }

          const columns = Array.isArray(table.structure.columns) ? table.structure.columns : [];
          const column = columns.find((item) => item && item.name === normalizedColumn);

          if (!column) {
            return;
          }

          column.foreign = normalizeForeignDefinition(foreign);
          this.setManualForeignError(table.name, normalizedColumn, '');
        },
        async openManualForeignModal(table, column) {
          if (!table || !column) {
            return;
          }

          const tableName = typeof table.name === 'string' ? table.name : '';
          const columnName = typeof column.name === 'string' ? column.name : '';

          if (!tableName || !columnName) {
            return;
          }

          await this.ensureStructureLoaded(table);

          this.manualForeignModal.open = true;
          this.manualForeignModal.error = null;
          this.manualForeignModal.saving = false;
          this.manualForeignModal.sourceTable = tableName;
          this.manualForeignModal.sourceColumn = columnName;

          const currentForeign = column.foreign && column.foreign.manual
            ? column.foreign
            : null;

          this.manualForeignModal.targetTable = currentForeign ? currentForeign.table : '';
          this.manualForeignModal.targetColumn = currentForeign ? currentForeign.column : '';
          this.manualForeignModal.displayColumn = currentForeign && currentForeign.displayColumn
            ? currentForeign.displayColumn
            : '';
          this.manualForeignModal.targetColumns = [];

          this.setManualForeignError(tableName, columnName, '');

          if (this.manualForeignModal.targetTable) {
            const targetTable = await this.ensureStructureLoadedByName(this.manualForeignModal.targetTable);
            this.manualForeignModal.targetColumns = this.getTableColumnNames(targetTable);
          }

          if (!Array.isArray(this.manualForeignModal.targetColumns)) {
            this.manualForeignModal.targetColumns = [];
          }

          if (this.manualForeignModal.targetColumn && !this.manualForeignModal.targetColumns.includes(this.manualForeignModal.targetColumn)) {
            this.manualForeignModal.targetColumn = '';
          }

          if (this.manualForeignModal.displayColumn && !this.manualForeignModal.targetColumns.includes(this.manualForeignModal.displayColumn)) {
            this.manualForeignModal.displayColumn = '';
          }
        },
        closeManualForeignModal() {
          this.manualForeignModal.open = false;
          this.manualForeignModal.sourceTable = '';
          this.manualForeignModal.sourceColumn = '';
          this.manualForeignModal.targetTable = '';
          this.manualForeignModal.targetColumn = '';
          this.manualForeignModal.displayColumn = '';
          this.manualForeignModal.targetColumns = [];
          this.manualForeignModal.error = null;
          this.manualForeignModal.saving = false;
        },
        async changeManualForeignTargetTable(value) {
          if (this.manualForeignModal.saving) {
            return;
          }

          const normalized = typeof value === 'string' ? value.trim() : '';
          this.manualForeignModal.targetTable = normalized;
          this.manualForeignModal.targetColumn = '';
          this.manualForeignModal.displayColumn = '';
          this.manualForeignModal.error = null;
          this.manualForeignModal.targetColumns = [];

          if (!normalized) {
            return;
          }

          const targetTable = await this.ensureStructureLoadedByName(normalized);
          this.manualForeignModal.targetColumns = this.getTableColumnNames(targetTable);
        },
        changeManualForeignTargetColumn(value) {
          if (this.manualForeignModal.saving) {
            return;
          }

          const normalized = typeof value === 'string' ? value.trim() : '';
          this.manualForeignModal.targetColumn = normalized;

          if (
            normalized &&
            (!this.manualForeignModal.displayColumn ||
              !this.manualForeignModal.targetColumns.includes(this.manualForeignModal.displayColumn))
          ) {
            this.manualForeignModal.displayColumn = normalized;
          }
        },
        changeManualForeignDisplayColumn(value) {
          if (this.manualForeignModal.saving) {
            return;
          }

          this.manualForeignModal.displayColumn = typeof value === 'string' ? value.trim() : '';
        },
        async saveManualForeign() {
          if (this.manualForeignModal.saving) {
            return;
          }

          const tableName = this.manualForeignModal.sourceTable;
          const columnName = this.manualForeignModal.sourceColumn;

          if (!tableName || !columnName) {
            this.manualForeignModal.error = 'Не вдалося визначити колонку для збереження зв\'язку.';
            return;
          }

          const routeTemplate = this.manualForeignRoutes.store;

          if (!routeTemplate) {
            this.manualForeignModal.error = 'Маршрут для збереження ручного зв\'язку не налаштовано.';
            return;
          }

          const targetTable = this.manualForeignModal.targetTable;
          const targetColumn = this.manualForeignModal.targetColumn;

          if (!targetTable || !targetColumn) {
            this.manualForeignModal.error = 'Оберіть таблицю та колонку для зв\'язку.';
            return;
          }

          const displayColumn = this.manualForeignModal.displayColumn;
          const route = this.getManualForeignRoute(routeTemplate, tableName, columnName);

          if (!route) {
            this.manualForeignModal.error = 'Маршрут для збереження ручного зв\'язку не налаштовано.';
            return;
          }

          const payload = {
            foreign_table: targetTable,
            foreign_column: targetColumn,
          };

          if (displayColumn) {
            payload.display_column = displayColumn;
          }

          this.manualForeignModal.saving = true;
          this.manualForeignModal.error = null;

          try {
            const response = await fetch(new URL(route, window.location.origin).toString(), {
              method: 'POST',
              headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken || '',
              },
              body: JSON.stringify(payload),
            });

            const data = await response.json().catch(() => ({}));

            if (!response.ok) {
              const message = data?.message || 'Не вдалося зберегти ручний зв\'язок.';
              throw new Error(message);
            }

            const sourceTable = this.findTableByName(tableName);

            if (sourceTable) {
              this.updateColumnForeign(sourceTable, columnName, data?.foreign ?? null);
            }

            this.manualForeignModal.saving = false;
            this.closeManualForeignModal();
          } catch (error) {
            this.manualForeignModal.saving = false;
            this.manualForeignModal.error = error?.message ?? 'Сталася помилка під час збереження ручного зв\'язку.';
          }
        },
        async deleteManualForeignFromModal() {
          if (this.manualForeignModal.saving) {
            return;
          }

          const tableName = this.manualForeignModal.sourceTable;
          const columnName = this.manualForeignModal.sourceColumn;

          if (!tableName || !columnName) {
            this.manualForeignModal.error = 'Не вдалося визначити колонку для видалення.';
            return;
          }

          const table = this.findTableByName(tableName);

          if (!table) {
            this.manualForeignModal.error = 'Не вдалося знайти таблицю для видалення зв\'язку.';
            return;
          }

          const columns = Array.isArray(table.structure?.columns) ? table.structure.columns : [];
          const column = columns.find((item) => item && item.name === columnName);

          if (!column) {
            this.manualForeignModal.error = 'Не вдалося знайти колонку для видалення зв\'язку.';
            return;
          }

          this.manualForeignModal.saving = true;
          this.manualForeignModal.error = null;

          const success = await this.removeManualForeign(table, column);

          this.manualForeignModal.saving = false;

          if (success) {
            this.closeManualForeignModal();
          } else {
            const errorMessage = this.getManualForeignError(tableName, columnName);
            this.manualForeignModal.error = errorMessage || 'Не вдалося видалити ручний зв\'язок.';
          }
        },
        async removeManualForeign(table, column) {
          if (!table || !column) {
            return false;
          }

          const tableName = typeof table.name === 'string' ? table.name : '';
          const columnName = typeof column.name === 'string' ? column.name : '';

          if (!tableName || !columnName) {
            return false;
          }

          const routeTemplate = this.manualForeignRoutes.delete;

          if (!routeTemplate) {
            this.setManualForeignError(tableName, columnName, 'Маршрут для видалення ручного зв\'язку не налаштовано.');
            return false;
          }

          const route = this.getManualForeignRoute(routeTemplate, tableName, columnName);

          if (!route) {
            this.setManualForeignError(tableName, columnName, 'Маршрут для видалення ручного зв\'язку не налаштовано.');
            return false;
          }

          try {
            const response = await fetch(new URL(route, window.location.origin).toString(), {
              method: 'DELETE',
              headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken || '',
              },
            });

            const data = await response.json().catch(() => ({}));

            if (!response.ok) {
              const message = data?.message || 'Не вдалося видалити ручний зв\'язок.';
              throw new Error(message);
            }

            this.updateColumnForeign(table, columnName, data?.foreign ?? null);

            return true;
          } catch (error) {
            const message = error?.message ?? 'Сталася помилка під час видалення ручного зв\'язку.';
            this.setManualForeignError(tableName, columnName, message);
            return false;
          }
        },
        async confirmManualForeignRemoval(table, column) {
          if (!table || !column) {
            return;
          }

          if (!window.confirm('Видалити ручний зв\'язок для цього поля?')) {
            return;
          }

          await this.removeManualForeign(table, column);
        },
        async toggleTable(table) {
          if (!table) {
            return;
          }

          table.open = !table.open;

          if (table.open) {
            await this.ensureStructureLoaded(table);
          }
        },
        async ensureStructureLoaded(table) {
          if (!table || !table.structure) {
            return;
          }

          if (table.structure.loading || table.structure.loaded) {
            return;
          }

          if (table.structure.error) {
            table.structure.error = null;
          }

          if (Array.isArray(table.structure.columns) && table.structure.columns.length > 0) {
            table.structure.loaded = true;
            return;
          }

          if (!this.structureRoute) {
            table.structure.loaded = true;
            return;
          }

          await this.loadTableStructure(table);
        },
        async loadTableStructure(table) {
          if (!table || !this.structureRoute) {
            return;
          }

          table.structure.loading = true;
          table.structure.error = null;

          try {
            const url = new URL(
              this.structureRoute.replace('__TABLE__', encodeURIComponent(table.name)),
              window.location.origin
            );

            const response = await fetch(url.toString(), {
              headers: {
                Accept: 'application/json',
              },
            });

            if (!response.ok) {
              const payload = await response.json().catch(() => null);
              const message = payload?.message || 'Не вдалося завантажити структуру таблиці.';
              throw new Error(message);
            }

            const data = await response.json();
            this.setTableColumns(table, data.columns);

            if (Object.prototype.hasOwnProperty.call(data, 'columns_count')) {
              const numericCount = Number(data.columns_count);
              if (!Number.isNaN(numericCount) && numericCount >= 0) {
                table.columnsCount = Math.max(table.columnsCount, Math.trunc(numericCount));
              }
            }
          } catch (error) {
            table.structure.error = error.message ?? 'Сталася помилка під час завантаження структури таблиці.';
            table.structure.loaded = false;
          } finally {
            table.structure.loading = false;
          }
        },
        setTableColumns(table, columns) {
          if (!table || !table.structure) {
            return;
          }

          const normalizedColumns = normalizeColumns(columns);
          table.structure.columns = normalizedColumns;
          table.structure.loaded = true;
          const currentCount = typeof table.columnsCount === 'number' && !Number.isNaN(table.columnsCount)
            ? table.columnsCount
            : 0;
          table.columnsCount = Math.max(currentCount, normalizedColumns.length);
          table.primaryKeys = normalizedColumns
            .filter((column) => column && column.key === 'PRI' && column.name)
            .map((column) => column.name);

          if (!Array.isArray(table.records.columns) || table.records.columns.length === 0) {
            table.records.columns = normalizedColumns.map((column) => column.name).filter(Boolean);
          }
        },
        async toggleRecords(table) {
          table.records.visible = !table.records.visible;
          table.records.error = null;

          if (table.records.visible && !table.records.loaded) {
            await this.loadRecords(table);
          }
        },
        async loadRecords(table) {
          const requestId = (table.records.requestId ?? 0) + 1;
          table.records.requestId = requestId;
          table.records.loading = true;
          table.records.error = null;
          table.records.loaded = false;

          try {
            const previousFilters = table.records.filters.map((filter) => ({ ...filter }));
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

            const searchQuery = typeof table.records.search === 'string' ? table.records.search.trim() : '';
            const searchColumn = typeof table.records.searchColumn === 'string'
              ? table.records.searchColumn.trim()
              : '';

            if (searchQuery) {
              url.searchParams.set('search', searchQuery);
            }

            if (searchColumn) {
              url.searchParams.set('search_column', searchColumn);
            }

            table.records.filters.forEach((filter, index) => {
              if (!filter || !filter.column || !filter.operator) {
                return;
              }

              url.searchParams.append(`filters[${index}][column]`, filter.column);
              url.searchParams.append(`filters[${index}][operator]`, filter.operator);

              if (this.operatorRequiresValue(filter.operator)) {
                const value = filter.value ?? '';
                url.searchParams.append(`filters[${index}][value]`, value === null ? '' : String(value));
              }
            });

            const response = await fetch(url.toString(), {
              headers: {
                Accept: 'application/json',
              },
            });

            if (!response.ok) {
              throw new Error('Не вдалося завантажити записи.');
            }

            const data = await response.json();

            if (table.records.requestId !== requestId) {
              return;
            }

            table.records.rows = data.rows || [];
            table.records.columns = Array.isArray(data.columns)
              ? data.columns.filter((name) => typeof name === 'string')
              : table.records.columns;
            table.records.page = data.page || 1;
            table.records.perPage = data.per_page || table.records.perPage;
            table.records.total = data.total ?? table.records.total;
            table.records.lastPage = data.last_page || 1;
            table.records.sort = data.sort || null;
            table.records.direction = data.direction || table.records.direction;
            const filtersFromResponse = Array.isArray(data.filters) ? data.filters : previousFilters;
            table.records.filters = this.normalizeFilters(filtersFromResponse, previousFilters);

            if (typeof data.search === 'string') {
              table.records.search = data.search;
              table.records.searchInput = data.search;
            }

            const responseSearchColumn = typeof data.search_column === 'string'
              ? data.search_column
              : table.records.searchColumn;
            table.records.searchColumn = responseSearchColumn || '';

            if (
              table.records.searchColumn &&
              Array.isArray(table.records.columns) &&
              !table.records.columns.includes(table.records.searchColumn)
            ) {
              table.records.searchColumn = '';
            }

            table.records.loaded = true;
          } catch (error) {
            if (table.records.requestId !== requestId) {
              return;
            }

            table.records.error = error.message ?? 'Сталася помилка під час завантаження записів.';
          } finally {
            if (table.records.requestId === requestId) {
              table.records.loading = false;
            }
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
        async deleteRecord(table, row, rowIndex) {
          if (table.records.loading) {
            return;
          }

          if (!row || typeof row !== 'object') {
            return;
          }

          if (!window.confirm('Ви впевнені, що хочете видалити цей запис?')) {
            return;
          }

          const identifiers = this.buildIdentifiers(table, row);

          if (identifiers.length === 0) {
            table.records.error = 'Не вдалося визначити ідентифікатори запису для видалення.';
            return;
          }

          table.records.error = null;
          table.records.deletingRowIndex = rowIndex;

          try {
            const url = new URL(
              this.recordsDeleteRoute.replace('__TABLE__', encodeURIComponent(table.name)),
              window.location.origin
            );

            const response = await fetch(url.toString(), {
              method: 'DELETE',
              headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken || '',
              },
              body: JSON.stringify({ identifiers }),
            });

            if (!response.ok) {
              const payload = await response.json().catch(() => null);
              const message = payload?.message || 'Не вдалося видалити запис.';
              throw new Error(message);
            }

            await this.loadRecords(table);
          } catch (error) {
            table.records.error = error.message ?? 'Сталася помилка під час видалення запису.';
          } finally {
            table.records.deletingRowIndex = null;
          }
        },
        buildIdentifiers(table, row) {
          const identifiers = [];
          const primaryKeys = Array.isArray(table.primaryKeys)
            ? table.primaryKeys.filter(Boolean)
            : [];
          const fallbackColumns = Array.isArray(table.structure?.columns)
            ? table.structure.columns.map((column) => column.name)
            : [];
          const columns = primaryKeys.length > 0
            ? primaryKeys
            : (Array.isArray(table.records.columns) && table.records.columns.length > 0
              ? table.records.columns
              : fallbackColumns);

          columns.forEach((column) => {
            if (!column || typeof column !== 'string') {
              return;
            }

            if (!Object.prototype.hasOwnProperty.call(row, column)) {
              return;
            }

            const value = row[column];

            if (value === undefined) {
              return;
            }

            if (value !== null && typeof value === 'object') {
              return;
            }

            identifiers.push({
              column,
              value,
            });
          });

          return identifiers;
        },
        findForeignKey(table, columnName) {
          if (!table || typeof columnName !== 'string' || !table.structure) {
            return null;
          }

          const normalizedColumn = columnName.trim();

          if (!normalizedColumn) {
            return null;
          }

          const columns = Array.isArray(table.structure.columns)
            ? table.structure.columns
            : [];

          const column = columns.find((item) => item && item.name === normalizedColumn);

          if (!column || !column.foreign) {
            return null;
          }

          const foreign = column.foreign;
          const foreignTable = typeof foreign.table === 'string' ? foreign.table : '';
          const foreignColumn = typeof foreign.column === 'string' ? foreign.column : '';

          if (!foreignTable || !foreignColumn) {
            return null;
          }

          const constraint = typeof foreign.constraint === 'string' && foreign.constraint !== ''
            ? foreign.constraint
            : null;
          const displayColumn = typeof foreign.displayColumn === 'string' && foreign.displayColumn !== ''
            ? foreign.displayColumn
            : null;
          const labelColumns = Array.isArray(foreign.labelColumns)
            ? foreign.labelColumns.filter((label) => typeof label === 'string' && label !== '')
            : [];

          return {
            table: foreignTable,
            column: foreignColumn,
            constraint,
            displayColumn,
            labelColumns,
          };
        },
        toggleForeignRecords() {
          if (!this.valueModal.foreignKey) {
            return;
          }

          const nextVisible = !this.valueModal.foreignRecords.visible;
          this.valueModal.foreignRecords.visible = nextVisible;

          if (nextVisible) {
            if (!this.valueModal.foreignRecords.loaded) {
              this.loadForeignRecords();
            }

            this.$nextTick(() => {
              this.scrollValueModalToTop();
            });
          } else {
            this.resetForeignRecordPreview();
            this.$nextTick(() => {
              this.autoResizeValueEditor();
            });
          }
        },
        resetForeignRecordPreview() {
          if (!this.valueModal.foreignRecords) {
            return;
          }

          this.valueModal.foreignRecords.preview = createForeignRecordPreviewState();
        },
        async loadForeignRecords(page = null) {
          const foreignKey = this.valueModal.foreignKey;

          if (!foreignKey) {
            return;
          }

          if (!this.recordsRoute) {
            this.valueModal.foreignRecords.error = 'Маршрут для завантаження записів не налаштовано.';
            this.valueModal.foreignRecords.loaded = false;
            return;
          }

          if (typeof page === 'number' && Number.isFinite(page)) {
            this.valueModal.foreignRecords.page = Math.max(1, Math.trunc(page));
          }

          const currentPage = this.valueModal.foreignRecords.page || 1;
          const perPage = this.valueModal.foreignRecords.perPage || 8;
          const requestId = (this.valueModal.foreignRecords.requestId ?? 0) + 1;

          this.valueModal.foreignRecords.requestId = requestId;
          this.valueModal.foreignRecords.loading = true;
          this.valueModal.foreignRecords.error = null;
          this.valueModal.foreignRecords.preview = createForeignRecordPreviewState();

          try {
            const url = new URL(
              this.recordsRoute.replace('__TABLE__', encodeURIComponent(foreignKey.table)),
              window.location.origin,
            );

            url.searchParams.set('page', currentPage);
            url.searchParams.set('per_page', perPage);

            const query = typeof this.valueModal.foreignRecords.query === 'string'
              ? this.valueModal.foreignRecords.query.trim()
              : '';
            const searchColumn = typeof this.valueModal.foreignRecords.searchColumn === 'string'
              ? this.valueModal.foreignRecords.searchColumn.trim()
              : '';

            if (query) {
              url.searchParams.set('search', query);
            }

            if (searchColumn) {
              url.searchParams.set('search_column', searchColumn);
            }

            const response = await fetch(url.toString(), {
              headers: {
                Accept: 'application/json',
              },
            });

            if (!response.ok) {
              const payload = await response.json().catch(() => null);
              const message = payload?.message || 'Не вдалося завантажити пов\'язані записи.';
              throw new Error(message);
            }

            const data = await response.json();

            if (this.valueModal.foreignRecords.requestId !== requestId) {
              return;
            }

            this.valueModal.foreignRecords.options = Array.isArray(data.rows) ? data.rows : [];
            this.valueModal.foreignRecords.columns = Array.isArray(data.columns)
              ? data.columns.filter((name) => typeof name === 'string' && name !== '')
              : [];
            const responseSearchColumn = typeof data.search_column === 'string'
              ? data.search_column
              : this.valueModal.foreignRecords.searchColumn;
            this.valueModal.foreignRecords.searchColumn = responseSearchColumn || '';

            if (
              this.valueModal.foreignRecords.searchColumn &&
              !this.valueModal.foreignRecords.columns.includes(this.valueModal.foreignRecords.searchColumn)
            ) {
              this.valueModal.foreignRecords.searchColumn = '';
            }
            this.valueModal.foreignRecords.page = data.page || currentPage;
            this.valueModal.foreignRecords.lastPage = data.last_page || 1;
            this.valueModal.foreignRecords.loaded = true;
          } catch (error) {
            if (this.valueModal.foreignRecords.requestId !== requestId) {
              return;
            }

            this.valueModal.foreignRecords.error = error.message ?? 'Сталася помилка під час завантаження пов’язаних записів.';
            this.valueModal.foreignRecords.loaded = false;
          } finally {
            if (this.valueModal.foreignRecords.requestId === requestId) {
              this.valueModal.foreignRecords.loading = false;
            }
          }
        },
        searchForeignRecords() {
          if (!this.valueModal.foreignKey) {
            return;
          }

          this.valueModal.foreignRecords.page = 1;
          this.loadForeignRecords(1);
        },
        updateForeignRecordsSearchColumn(column) {
          if (!this.valueModal.foreignKey) {
            return;
          }

          const normalized = typeof column === 'string' ? column.trim() : '';
          const previous = typeof this.valueModal.foreignRecords.searchColumn === 'string'
            ? this.valueModal.foreignRecords.searchColumn.trim()
            : '';

          if (normalized === previous && this.valueModal.foreignRecords.loaded) {
            this.valueModal.foreignRecords.searchColumn = normalized;
            return;
          }

          this.valueModal.foreignRecords.searchColumn = normalized;
          this.valueModal.foreignRecords.page = 1;
          this.loadForeignRecords(1);
        },
        changeForeignRecordsPage(page) {
          if (this.valueModal.foreignRecords.loading) {
            return;
          }

          const numericPage = Number(page);

          if (!Number.isFinite(numericPage)) {
            return;
          }

          const lastPage = this.valueModal.foreignRecords.lastPage || 1;
          const target = Math.min(Math.max(Math.trunc(numericPage), 1), lastPage);

          if (target === (this.valueModal.foreignRecords.page || 1)) {
            return;
          }

          this.valueModal.foreignRecords.page = target;
          this.loadForeignRecords(target);
        },
        selectForeignRecord(record) {
          const foreignKey = this.valueModal.foreignKey;

          if (!foreignKey || !record || typeof record !== 'object') {
            return;
          }

          const value = record[foreignKey.column];

          if (value === undefined) {
            return;
          }

          this.valueModal.editValue = value === null ? '' : String(value);
          this.valueModal.foreignRecords.selectedValue = this.normalizeForeignSelectionValue(value);
          this.resetForeignRecordPreview();
          this.$nextTick(() => {
            this.autoResizeValueEditor();
          });
        },
        foreignRecordKey(record) {
          const foreignKey = this.valueModal.foreignKey;

          if (foreignKey && record && typeof record === 'object') {
            const value = record[foreignKey.column];

            if (value !== undefined && value !== null) {
              return `${foreignKey.table}:${foreignKey.column}:${String(value)}`;
            }
          }

          try {
            return JSON.stringify(record ?? {});
          } catch (error) {
            return String(Math.random());
          }
        },
        isForeignRecordSelected(record) {
          const foreignKey = this.valueModal.foreignKey;

          if (!foreignKey || !record || typeof record !== 'object') {
            return false;
          }

          const value = record[foreignKey.column];
          const normalized = this.normalizeForeignSelectionValue(value);

          if (normalized === '') {
            return false;
          }

          return normalized === this.valueModal.foreignRecords.selectedValue;
        },
        isForeignRecordPreviewed(record) {
          const preview = this.valueModal.foreignRecords?.preview;

          if (!preview) {
            return false;
          }

          const key = this.foreignRecordKey(record);

          return (
            preview.visible &&
            typeof preview.key === 'string' &&
            preview.key !== '' &&
            preview.key === key
          );
        },
        buildForeignRecordIdentifiers(record) {
          const foreignKey = this.valueModal.foreignKey;

          if (!foreignKey || !record || typeof record !== 'object') {
            return [];
          }

          const value = record[foreignKey.column];

          if (value !== null && typeof value === 'object') {
            return [];
          }

          if (value === undefined) {
            return [];
          }

          return [
            {
              column: foreignKey.column,
              value,
            },
          ];
        },
        async previewForeignRecord(record) {
          const foreignKey = this.valueModal.foreignKey;

          if (!foreignKey || !record || typeof record !== 'object') {
            return;
          }

          const key = this.foreignRecordKey(record);

          if (!key) {
            return;
          }

          const preview = this.valueModal.foreignRecords.preview;

          if (preview.visible && preview.key === key) {
            this.resetForeignRecordPreview();
            return;
          }

          const identifiers = this.buildForeignRecordIdentifiers(record);

          if (identifiers.length === 0) {
            this.valueModal.foreignRecords.preview = {
              ...createForeignRecordPreviewState(),
              visible: true,
              key,
              error: 'Не вдалося визначити ідентифікатори пов’язаного запису.',
            };
            return;
          }

          if (!this.recordsShowRoute) {
            this.valueModal.foreignRecords.preview = {
              ...createForeignRecordPreviewState(),
              visible: true,
              key,
              error: 'Маршрут для завантаження повного запису не налаштовано.',
            };
            return;
          }

          const requestId = (preview.requestId ?? 0) + 1;

          this.valueModal.foreignRecords.preview = {
            ...createForeignRecordPreviewState(),
            visible: true,
            key,
            loading: true,
            requestId,
          };

          try {
            const url = new URL(
              this.recordsShowRoute.replace('__TABLE__', encodeURIComponent(foreignKey.table)),
              window.location.origin,
            );

            const response = await fetch(url.toString(), {
              method: 'POST',
              headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken || '',
              },
              body: JSON.stringify({
                identifiers,
              }),
            });

            if (!response.ok) {
              const payload = await response.json().catch(() => null);
              const message = payload?.message || 'Не вдалося завантажити повний запис.';
              throw new Error(message);
            }

            const data = await response.json();

            if (this.valueModal.foreignRecords.preview.requestId !== requestId) {
              return;
            }

            const recordData = data && typeof data === 'object' && !Array.isArray(data.record)
              ? (data.record ?? {})
              : {};
            const normalizedRecord = recordData && typeof recordData === 'object' ? recordData : {};
            const columns = Array.isArray(data?.columns)
              ? data.columns.filter((column) => typeof column === 'string' && column !== '')
              : Object.keys(normalizedRecord);

            this.valueModal.foreignRecords.preview.loading = false;
            this.valueModal.foreignRecords.preview.error = null;
            this.valueModal.foreignRecords.preview.record = normalizedRecord;
            this.valueModal.foreignRecords.preview.columns = columns;
          } catch (error) {
            if (this.valueModal.foreignRecords.preview.requestId !== requestId) {
              return;
            }

            this.valueModal.foreignRecords.preview.loading = false;
            this.valueModal.foreignRecords.preview.error = error.message ?? 'Сталася помилка під час завантаження запису.';
          } finally {
            if (this.valueModal.foreignRecords.preview.requestId === requestId) {
              this.valueModal.foreignRecords.preview.loading = false;
            }

            this.$nextTick(() => {
              this.scrollValueModalToTop();
            });
          }
        },
        formatForeignRecordLabel(record) {
          const foreignKey = this.valueModal.foreignKey;

          if (!foreignKey || !record || typeof record !== 'object') {
            return '';
          }

          const labelColumns = Array.isArray(foreignKey.labelColumns) && foreignKey.labelColumns.length > 0
            ? foreignKey.labelColumns
            : [];

          const candidates = labelColumns.length > 0
            ? labelColumns
            : [foreignKey.displayColumn, foreignKey.column].filter((column) => typeof column === 'string' && column);

          for (const column of candidates) {
            if (!column || !Object.prototype.hasOwnProperty.call(record, column)) {
              continue;
            }

            const value = record[column];
            const formatted = this.formatCell(value);

            if (formatted && formatted !== '—') {
              return formatted;
            }
          }

          return this.formatCell(record[foreignKey.column]);
        },
        formatForeignRecordSummary(record) {
          const foreignKey = this.valueModal.foreignKey;

          if (!foreignKey || !record || typeof record !== 'object') {
            return '';
          }

          const columns = Array.isArray(this.valueModal.foreignRecords.columns)
            ? this.valueModal.foreignRecords.columns.filter((name) => typeof name === 'string' && name !== '')
            : Object.keys(record);

          const summary = [];

          columns.forEach((column) => {
            if (column === foreignKey.column) {
              return;
            }

            if (!Object.prototype.hasOwnProperty.call(record, column)) {
              return;
            }

            const value = record[column];
            const formatted = this.formatCell(value);

            if (!formatted || formatted === '—') {
              return;
            }

            summary.push(`${column}: ${formatted}`);
          });

          return summary.slice(0, 3).join(' • ');
        },
        normalizeForeignSelectionValue(value) {
          if (value === null || value === undefined) {
            return '';
          }

          if (typeof value === 'object') {
            try {
              return JSON.stringify(value);
            } catch (error) {
              return '';
            }
          }

          return String(value);
        },
        syncForeignSelectionWithEditValue() {
          if (!this.valueModal.foreignKey) {
            this.valueModal.foreignRecords.selectedValue = '';
            return;
          }

          this.valueModal.foreignRecords.selectedValue = this.normalizeForeignSelectionValue(
            this.valueModal.editValue,
          );
        },
        syncForeignSelectionWithRawValue(value) {
          if (!this.valueModal.foreignKey) {
            this.valueModal.foreignRecords.selectedValue = '';
            return;
          }

          this.valueModal.foreignRecords.selectedValue = this.normalizeForeignSelectionValue(value);
        },
        scrollValueModalToTop() {
          const overlay = this.$refs?.valueModalOverlay;

          if (!overlay) {
            return;
          }

          overlay.scrollTop = 0;
        },
        autoResizeValueEditor(element = null) {
          const textarea = element || this.$refs?.valueEditor;

          if (!textarea) {
            return;
          }

          requestAnimationFrame(() => {
            textarea.style.height = 'auto';
            textarea.style.height = `${textarea.scrollHeight}px`;

            if (this.valueModal.editing) {
              this.scrollValueModalToTop();
            }
          });
        },
        async showRecordValue(table, column, row) {
          const columnName = typeof column === 'string' ? column.trim() : '';
          const tableName = table && typeof table.name === 'string' ? table.name : '';

          if (!columnName || !tableName) {
            return;
          }

          const identifiers = this.buildIdentifiers(table, row);
          const clonedIdentifiers = identifiers
            .filter((identifier) => identifier && typeof identifier.column === 'string' && identifier.column !== '')
            .map((identifier) => ({
              column: identifier.column,
              value: identifier.value,
            }));

          this.valueModal.open = true;
          this.valueModal.loading = true;
          this.valueModal.error = null;
          this.valueModal.updateError = null;
          this.valueModal.table = tableName;
          this.valueModal.column = columnName;
          this.valueModal.value = '';
          this.valueModal.rawValue = null;
          this.valueModal.editValue = '';
          this.valueModal.editing = false;
          this.valueModal.saving = false;
          this.valueModal.identifiers = clonedIdentifiers;
          this.valueModal.foreignKey = null;
          this.valueModal.foreignRecords = createForeignRecordsState();

          const records = table && typeof table === 'object' ? table.records || {} : {};
          const searchTerm = typeof records.search === 'string' ? records.search : '';
          const searchColumn = typeof records.searchColumn === 'string' ? records.searchColumn.trim() : '';
          this.valueModal.searchTerm = !searchTerm || (searchColumn && searchColumn !== columnName)
            ? ''
            : searchTerm;

          if (clonedIdentifiers.length === 0) {
            this.valueModal.loading = false;
            this.valueModal.error = 'Не вдалося визначити ідентифікатори запису для отримання значення.';
            return;
          }

          if (!this.recordsValueRoute) {
            this.valueModal.loading = false;
            this.valueModal.error = 'Маршрут для отримання значення не налаштовано.';
            return;
          }

          await this.ensureStructureLoaded(table);
          this.valueModal.foreignKey = this.findForeignKey(table, columnName);
          this.valueModal.foreignRecords = createForeignRecordsState();

          try {
            const url = new URL(
              this.recordsValueRoute.replace('__TABLE__', encodeURIComponent(tableName)),
              window.location.origin
            );

            const response = await fetch(url.toString(), {
              method: 'POST',
              headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken || '',
              },
              body: JSON.stringify({
                column: columnName,
                identifiers: clonedIdentifiers,
              }),
            });

            if (!response.ok) {
              const payload = await response.json().catch(() => null);
              const message = payload?.message || 'Не вдалося отримати повне значення.';
              throw new Error(message);
            }

            const payload = await response.json();
            const rawValue = payload && Object.prototype.hasOwnProperty.call(payload, 'value')
              ? payload.value
              : null;

            this.valueModal.rawValue = rawValue;
            this.valueModal.value = this.formatCell(rawValue);
            this.valueModal.editValue = this.prepareEditableValue(rawValue);
            this.syncForeignSelectionWithRawValue(rawValue);
          } catch (error) {
            this.valueModal.error = error.message ?? 'Сталася помилка під час отримання значення.';
          } finally {
            this.valueModal.loading = false;
          }
        },
        startEditingValue() {
          if (this.valueModal.loading || this.valueModal.error) {
            return;
          }

          this.valueModal.editValue = this.prepareEditableValue(this.valueModal.rawValue);
          this.valueModal.editing = true;
          this.valueModal.updateError = null;
          this.syncForeignSelectionWithEditValue();
          this.$nextTick(() => {
            this.scrollValueModalToTop();
            this.autoResizeValueEditor();
          });
        },
        cancelEditingValue() {
          if (this.valueModal.saving) {
            return;
          }

          this.valueModal.editValue = this.prepareEditableValue(this.valueModal.rawValue);
          this.valueModal.editing = false;
          this.valueModal.updateError = null;
          this.valueModal.foreignRecords.visible = false;
          this.syncForeignSelectionWithRawValue(this.valueModal.rawValue);
        },
        async saveEditedValue() {
          if (this.valueModal.saving || this.valueModal.loading) {
            return;
          }

          const tableName = typeof this.valueModal.table === 'string' ? this.valueModal.table : '';
          const columnName = typeof this.valueModal.column === 'string' ? this.valueModal.column : '';
          const identifiers = Array.isArray(this.valueModal.identifiers)
            ? this.valueModal.identifiers
              .filter((identifier) => identifier && typeof identifier.column === 'string' && identifier.column !== '')
              .map((identifier) => ({
                column: identifier.column,
                value: identifier.value,
              }))
            : [];

          if (!tableName || !columnName) {
            this.valueModal.updateError = 'Невідомо, яке значення оновлювати.';
            return;
          }

          if (identifiers.length === 0) {
            this.valueModal.updateError = 'Не вдалося визначити ідентифікатори запису для збереження.';
            return;
          }

          if (!this.recordsUpdateRoute) {
            this.valueModal.updateError = 'Маршрут для збереження значення не налаштовано.';
            return;
          }

          this.valueModal.saving = true;
          this.valueModal.updateError = null;

          try {
            const url = new URL(
              this.recordsUpdateRoute.replace('__TABLE__', encodeURIComponent(tableName)),
              window.location.origin
            );

            const response = await fetch(url.toString(), {
              method: 'PUT',
              headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken || '',
              },
              body: JSON.stringify({
                column: columnName,
                value: this.valueModal.editValue,
                identifiers,
              }),
            });

            if (!response.ok) {
              const payload = await response.json().catch(() => null);
              const message = payload?.message || 'Не вдалося зберегти значення.';
              throw new Error(message);
            }

            const payload = await response.json();
            const updatedValue = payload && Object.prototype.hasOwnProperty.call(payload, 'value')
              ? payload.value
              : null;

            this.valueModal.rawValue = updatedValue;
            this.valueModal.value = this.formatCell(updatedValue);
            this.valueModal.editValue = this.prepareEditableValue(updatedValue);
            this.valueModal.editing = false;
            this.valueModal.updateError = null;
            this.syncForeignSelectionWithRawValue(updatedValue);

            this.valueModal.identifiers = identifiers.map((identifier) => {
              if (identifier.column === columnName) {
                return {
                  column: identifier.column,
                  value: updatedValue,
                };
              }

              return identifier;
            });
          } catch (error) {
            this.valueModal.updateError = error.message ?? 'Сталася помилка під час збереження значення.';
          } finally {
            this.valueModal.saving = false;
          }
        },
        closeValueModal() {
          this.valueModal.open = false;
          this.valueModal.loading = false;
          this.valueModal.error = null;
          this.valueModal.updateError = null;
          this.valueModal.value = '';
          this.valueModal.rawValue = null;
          this.valueModal.table = '';
          this.valueModal.column = '';
          this.valueModal.searchTerm = '';
          this.valueModal.editing = false;
          this.valueModal.editValue = '';
          this.valueModal.saving = false;
          this.valueModal.identifiers = [];
          this.valueModal.foreignKey = null;
          this.valueModal.foreignRecords = createForeignRecordsState();
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
        addFilter(table) {
          if (table.records.loading) {
            return;
          }

          const fallbackColumn = Array.isArray(table.records.columns) && table.records.columns.length > 0
            ? table.records.columns[0]
            : (Array.isArray(table.structure?.columns) && table.structure.columns.length > 0
              ? table.structure.columns[0].name
              : '');

          table.records.filters = [
            ...table.records.filters,
            {
              id: this.generateFilterId(),
              column: fallbackColumn || '',
              operator: '=',
              value: '',
            },
          ];
        },
        removeFilter(table, index) {
          if (table.records.loading) {
            return;
          }

          if (index < 0 || index >= table.records.filters.length) {
            return;
          }

          table.records.filters = table.records.filters.filter((_, filterIndex) => filterIndex !== index);

          if (table.records.loaded) {
            table.records.page = 1;
            this.loadRecords(table);
          }
        },
        applyFilters(table) {
          if (table.records.loading) {
            return;
          }

          table.records.page = 1;
          this.loadRecords(table);
        },
        resetFilters(table) {
          if (table.records.loading || table.records.filters.length === 0) {
            return;
          }

          table.records.filters = [];
          table.records.page = 1;
          this.loadRecords(table);
        },
        updateSearch(table, value) {
          const rawValue = typeof value === 'string' ? value : '';
          const normalized = rawValue.trim();

          table.records.searchInput = rawValue;

          if (table.records.search === normalized && table.records.page === 1 && table.records.loaded) {
            return;
          }

          table.records.search = normalized;
          table.records.page = 1;
          this.loadRecords(table);
        },
        updateSearchColumn(table, value) {
          const normalized = typeof value === 'string' ? value.trim() : '';
          const previous = typeof table.records.searchColumn === 'string'
            ? table.records.searchColumn.trim()
            : '';

          if (previous === normalized && table.records.page === 1 && table.records.loaded) {
            table.records.searchColumn = normalized;
            return;
          }

          table.records.searchColumn = normalized;
          table.records.page = 1;
          this.loadRecords(table);
        },
        normalizeFilters(filters, previousFilters = []) {
          if (!Array.isArray(filters)) {
            return [];
          }

          const idsByKey = new Map();

          previousFilters.forEach((filter) => {
            if (!filter) {
              return;
            }

            const key = this.filterKey(filter);
            const existing = idsByKey.get(key) || [];
            existing.push(filter.id);
            idsByKey.set(key, existing);
          });

          return filters
            .filter((filter) => filter && typeof filter === 'object')
            .map((filter) => {
              const column = typeof filter.column === 'string' ? filter.column : '';
              const operator = typeof filter.operator === 'string' ? filter.operator : '=';
              const rawValue = filter.value ?? '';
              const value = rawValue === null ? '' : String(rawValue);
              const key = this.filterKey({ column, operator, value });
              const pool = idsByKey.get(key) || [];
              const id = pool.shift() ?? this.generateFilterId();

              if (pool.length > 0) {
                idsByKey.set(key, pool);
              } else {
                idsByKey.delete(key);
              }

              return {
                id,
                column,
                operator,
                value,
              };
            });
        },
        filterKey(filter) {
          const column = typeof filter.column === 'string' ? filter.column : '';
          const operator = typeof filter.operator === 'string' ? filter.operator : '';
          const value = filter.value === undefined || filter.value === null ? '' : String(filter.value);

          return [column, operator, value].join('::');
        },
        operatorRequiresValue(operator) {
          const normalized = (operator ?? '').toString().toLowerCase();

          return ['=', '!=', '<', '<=', '>', '>=', 'like', 'not like', '<>'].includes(normalized);
        },
        generateFilterId() {
          if (window.crypto && typeof window.crypto.randomUUID === 'function') {
            return window.crypto.randomUUID();
          }

          return `filter-${Date.now()}-${Math.random().toString(16).slice(2)}`;
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
        prepareEditableValue(value) {
          if (value === null || value === undefined) {
            return '';
          }

          if (typeof value === 'object') {
            try {
              return JSON.stringify(value, null, 2);
            } catch (error) {
              return String(value);
            }
          }

          return String(value);
        },
        truncateText(value, limit = 120) {
          const text = value === null || value === undefined ? '' : String(value);
          const numericLimit = Number(limit);

          if (!Number.isFinite(numericLimit) || numericLimit <= 0) {
            return text;
          }

          if (text.length <= numericLimit) {
            return text;
          }

          const ellipsis = '…';
          const sliceLength = Math.max(0, numericLimit - ellipsis.length);

          return `${text.slice(0, sliceLength)}${ellipsis}`;
        },
        renderRecordPreview(table, column, value) {
          const text = this.formatCell(value);
          const truncated = this.truncateText(text, this.cellPreviewLimit);
          const records = table && typeof table === 'object' ? table.records || {} : {};
          const searchTerm = typeof records.search === 'string' ? records.search : '';
          const selectedColumn = typeof records.searchColumn === 'string' ? records.searchColumn.trim() : '';
          const columnName = typeof column === 'string' ? column.trim() : '';

          if (!searchTerm || (selectedColumn && selectedColumn !== columnName)) {
            return this.escapeHtml(truncated);
          }

          return this.highlightText(truncated, searchTerm);
        },
        highlightQuery(value) {
          return this.highlightText(value, this.query);
        },
        highlightForeignRecordText(value) {
          const query =
            this.valueModal && this.valueModal.foreignRecords && typeof this.valueModal.foreignRecords.query === 'string'
              ? this.valueModal.foreignRecords.query
              : '';

          return this.highlightText(value, query);
        },
        highlightText(value, term) {
          const stringValue = value === null || value === undefined ? '' : String(value);
          const searchTerm = typeof term === 'string' ? term.trim() : '';

          if (!searchTerm) {
            return this.escapeHtml(stringValue);
          }

          const escapedSearch = this.escapeRegExp(searchTerm);

          if (!escapedSearch) {
            return this.escapeHtml(stringValue);
          }

          const regex = new RegExp(`(${escapedSearch})`, 'gi');
          const parts = stringValue.split(regex);

          return parts
            .map((part, index) => {
              if (index % 2 === 1 && part.length > 0) {
                return `<mark class="rounded bg-amber-200/70 px-1 py-0.5 text-amber-900">${this.escapeHtml(part)}</mark>`;
              }

              return this.escapeHtml(part);
            })
            .join('');
        },
        escapeHtml(value) {
          return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
        },
        escapeRegExp(value) {
          return String(value).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        },
      };
    };

    document.addEventListener('alpine:init', () => {
      if (window.Alpine && typeof window.Alpine.data === 'function') {
        window.Alpine.data('databaseStructureViewer', window.databaseStructureViewer);
      }
    });
  </script>
@endpush
