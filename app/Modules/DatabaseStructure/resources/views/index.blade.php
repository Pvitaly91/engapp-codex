@extends('layouts.app')

@section('title', 'Структура бази даних')

@section('content')
  @php
    $currentTab = ($activeTab ?? 'structure') === 'content-management' ? 'content-management' : 'structure';
    $standaloneTab = in_array($standaloneTab ?? null, ['structure', 'content-management'], true)
      ? $standaloneTab
      : null;
    $contentManagementTableSettings = $contentManagementTableSettings ?? config('database-structure.content_management.table_settings', []);
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
        'menuUpdate' => route('database-structure.content-management.menu.update'),
        'menuDelete' => route('database-structure.content-management.menu.destroy', ['table' => '__TABLE__']),
      ]),
      @js($contentManagementTableSettings),
      @js([
        'index' => route('database-structure.filters.index', ['table' => '__TABLE__', 'scope' => '__SCOPE__']),
        'store' => route('database-structure.filters.store', ['table' => '__TABLE__', 'scope' => '__SCOPE__']),
        'use' => route('database-structure.filters.use', ['table' => '__TABLE__', 'scope' => '__SCOPE__', 'filter' => '__FILTER__']),
        'default' => route('database-structure.filters.default', ['table' => '__TABLE__', 'scope' => '__SCOPE__']),
        'destroy' => route('database-structure.filters.destroy', ['table' => '__TABLE__', 'scope' => '__SCOPE__', 'filter' => '__FILTER__']),
      ]),
      @js(route('database-structure.keyword-search')),
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

    <section class="rounded-3xl border border-border/70 bg-background/60 p-6 shadow-soft space-y-4">
      <div class="flex flex-col gap-3 lg:flex-row lg:items-end">
        <div class="flex-1 space-y-2">
          <label class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Пошук за ключовим словом</label>
          <div class="relative">
            <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-muted-foreground">
              <i class="fa-solid fa-magnifying-glass text-sm"></i>
            </span>
            <input
              type="search"
              class="w-full rounded-2xl border border-input bg-background py-2 pl-10 pr-4 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
              placeholder="Введіть слово для пошуку по всіх таблицях..."
              x-model="globalSearch.keyword"
              @keyup.enter="performGlobalKeywordSearch()"
            />
          </div>
          <p class="text-[12px] text-muted-foreground">
            Запит виконає пошук по всіх таблицях та стовпцях активної бази та покаже перші збіги.
          </p>
        </div>
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
          <label class="text-xs font-semibold uppercase tracking-wide text-muted-foreground flex flex-col gap-1">
            <span>К-сть записів на таблицю</span>
            <select
              class="rounded-xl border border-input bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
              x-model.number="globalSearch.limit"
            >
              <option value="3">3</option>
              <option value="5">5</option>
              <option value="10">10</option>
            </select>
          </label>
          <button
            type="button"
            class="inline-flex items-center gap-2 rounded-full border border-primary bg-primary px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/40 disabled:cursor-not-allowed disabled:opacity-60"
            :disabled="globalSearch.loading || !(globalSearch.keyword && globalSearch.keyword.trim())"
            @click="performGlobalKeywordSearch()"
          >
            <i class="fa-solid fa-search"></i>
            Знайти
          </button>
          <button
            type="button"
            class="inline-flex items-center gap-2 rounded-full border border-border/60 bg-background px-4 py-2 text-sm font-medium text-muted-foreground transition hover:border-primary/60 hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary/40 disabled:cursor-not-allowed disabled:opacity-60"
            :disabled="globalSearch.loading && !globalSearch.keyword"
            @click="clearGlobalKeywordSearch()"
          >
            <i class="fa-solid fa-rotate-left"></i>
            Очистити
          </button>
        </div>
      </div>
      <template x-if="globalSearch.error">
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm text-rose-600" x-text="globalSearch.error"></div>
      </template>
      <div
        class="rounded-2xl border border-dashed border-border/60 bg-muted/30 px-4 py-3 text-sm text-muted-foreground"
        x-show="globalSearch.loading"
        x-cloak
      >
        Виконуємо пошук...
      </div>
      <div class="space-y-4" x-show="!globalSearch.loading && globalSearch.results.length > 0" x-cloak>
        <template x-for="result in globalSearch.results" :key="result.table">
          <div class="rounded-2xl border border-border/70 bg-card/70 p-4 shadow-soft/40">
            <div 
              class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between cursor-pointer"
              @click="toggleGlobalSearchResult(result)"
            >
              <div>
                <p class="text-[12px] font-semibold uppercase tracking-wide text-muted-foreground">Таблиця</p>
                <h3 class="text-lg font-semibold text-foreground" x-text="result.table"></h3>
              </div>
              <div class="flex items-center gap-2">
                <span class="inline-flex items-center rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">
                  <i class="fa-solid fa-database mr-1"></i>
                  <span x-text="`${result.total} збігів`"></span>
                </span>
                <i class="fa-solid fa-chevron-down text-muted-foreground transition-transform duration-200" :class="{ 'rotate-180': result.open }"></i>
              </div>
            </div>
            <div class="mt-4 space-y-3" x-show="result.open" x-collapse>
              <template x-for="(row, rowIndex) in result.rows" :key="`${result.table}-${rowIndex}`">
                <div class="rounded-2xl border border-border/60 bg-background/70 p-4">
                  <dl class="grid gap-3 sm:grid-cols-2">
                    <template x-for="(entry, entryIndex) in globalSearchRowEntries(row)" :key="`${result.table}-${rowIndex}-${entry.key}-${entryIndex}`">
                      <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-muted-foreground" x-text="entry.key"></dt>
                        <dd class="mt-1 text-sm text-foreground" x-html="highlightText(entry.value, globalSearch.keyword)"></dd>
                      </div>
                    </template>
                  </dl>
                </div>
              </template>
            </div>
          </div>
        </template>
      </div>
      <div
        class="rounded-2xl border border-dashed border-border/60 bg-muted/20 px-4 py-3 text-sm text-muted-foreground"
        x-show="globalSearch.completed && !globalSearch.loading && globalSearch.results.length === 0"
        x-cloak
      >
        Немає збігів для вказаного запиту.
      </div>
    </section>

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
                <div class="flex w-full flex-col gap-2 rounded-2xl border border-border/60 bg-background/70 p-4 text-[13px] font-semibold uppercase tracking-wide text-muted-foreground/80">
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
                <div class="rounded-2xl border border-border/60 bg-muted/20 p-4 text-[15px] text-muted-foreground">
                  <div class="flex flex-col gap-4">
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                      <h3 class="text-sm font-semibold text-foreground">
                        <button
                          type="button"
                          class="inline-flex items-center gap-2 text-sm font-semibold text-foreground transition hover:text-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
                          @click.stop="toggleRecordsFilters(table)"
                          :aria-expanded="table.records.filtersOpen ? 'true' : 'false'"
                          :aria-controls="`records-filters-${(table.name || '').replace(/[^A-Za-z0-9_-]/g, '-')}`"
                        >
                          <i
                            class="fa-solid fa-chevron-down text-xs transition-transform duration-200"
                            :class="table.records.filtersOpen ? 'rotate-180 text-primary' : 'text-muted-foreground'"
                          ></i>
                          <span>Фільтри записів</span>
                        </button>
                      </h3>
                      <div
                        class="flex flex-col items-start gap-2 md:items-end"
                        x-show="table.records.filtersOpen"
                        x-transition
                        x-cloak
                      >
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
                            @click.stop="saveRecordsFilters(table)"
                          >
                            <i class="fa-solid fa-floppy-disk text-[10px]"></i>
                            Зберегти фільтр
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
                        <template x-if="table.records.feedback">
                          <span class="text-xs font-semibold text-emerald-600 md:text-right" x-text="table.records.feedback"></span>
                        </template>
                        <template x-if="table.records.savedFilters.loading">
                          <span class="text-[12px] text-muted-foreground md:text-right">Завантаження збережених фільтрів…</span>
                        </template>
                        <template x-if="!table.records.savedFilters.loading && table.records.savedFilters.items.length > 0">
                          <div class="flex flex-wrap items-center gap-2 pt-1 md:justify-end">
                            <template x-for="saved in table.records.savedFilters.items" :key="`records-saved-${saved.id}`">
                              <div class="inline-flex items-center overflow-hidden rounded-full border border-border/70 bg-background text-xs font-semibold">
                                <button
                                  type="button"
                                  class="px-3 py-1 transition hover:bg-primary/10 hover:text-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 disabled:cursor-not-allowed disabled:opacity-60"
                                  :class="table.records.savedFilters.defaultId === saved.id ? 'bg-primary/10 text-primary' : 'text-foreground'"
                                  :disabled="table.records.loading || table.records.savedFilters.loading"
                                  @click.stop="applySavedRecordsFilterButton(table, saved.id)"
                                  x-text="saved.name"
                                ></button>
                                <button
                                  type="button"
                                  class="px-2 py-1 transition hover:text-amber-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-300 disabled:cursor-not-allowed disabled:opacity-60"
                                  :class="table.records.savedFilters.defaultId === saved.id ? 'text-amber-500' : 'text-muted-foreground'"
                                  :disabled="table.records.savedFilters.loading"
                                  @click.stop="toggleRecordsDefaultFilter(table, saved.id)"
                                  :aria-label="table.records.savedFilters.defaultId === saved.id ? `Вимкнути фільтр ${saved.name} за замовчуванням` : `Зробити фільтр ${saved.name} за замовчуванням`"
                                >
                                  <i class="fa-solid fa-star text-[10px]"></i>
                                </button>
                                <button
                                  type="button"
                                  class="px-2 py-1 text-muted-foreground transition hover:text-rose-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-300 disabled:cursor-not-allowed disabled:opacity-60"
                                  :disabled="table.records.savedFilters.loading"
                                  @click.stop="deleteSavedRecordsFilter(table, saved.id)"
                                  :aria-label="`Видалити фільтр ${saved.name}`"
                                >
                                  <i class="fa-solid fa-xmark text-[10px]"></i>
                                </button>
                              </div>
                            </template>
                            <button
                              type="button"
                              class="inline-flex items-center gap-2 rounded-full border border-border/70 bg-background px-3 py-1 text-xs font-semibold text-muted-foreground transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 disabled:cursor-not-allowed disabled:opacity-60"
                              :class="(!table.records.savedFilters.defaultId || table.records.savedFilters.defaultDisabled) ? 'border-primary/40 bg-primary/10 text-primary' : 'hover:border-primary/60 hover:text-primary'"
                              :aria-pressed="!table.records.savedFilters.defaultId || table.records.savedFilters.defaultDisabled"
                              :disabled="table.records.savedFilters.loading"
                              @click.stop="setRecordsDefaultFilter(table, '', { resetActive: true })"
                            >
                              <i class="fa-solid fa-ban text-[10px]"></i>
                              <span>All</span>
                            </button>
                          </div>
                        </template>
                      </div>
                    </div>
                    <div
                      x-show="table.records.filtersOpen"
                      x-collapse
                      x-cloak
                      :id="`records-filters-${(table.name || '').replace(/[^A-Za-z0-9_-]/g, '-')}`"
                    >
                      <p class="text-[15px] text-muted-foreground">
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

    </div>


    <div
      x-show="filterNameModal.open"
      x-cloak
      class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6"
      role="dialog"
      aria-modal="true"
    >
      <div
        class="absolute inset-0 bg-background/80 backdrop-blur-sm"
        @click="cancelFilterNameModal()"
      ></div>
      <div class="relative z-10 w-full max-w-md rounded-3xl border border-border/70 bg-white p-6 shadow-xl">
        <div class="flex items-start justify-between gap-4">
          <div>
            <h2 class="text-lg font-semibold text-foreground">Збереження фільтру</h2>
            <p class="mt-1 text-xs text-muted-foreground">
              Вкажіть назву для збереженого фільтру.
            </p>
          </div>
          <button
            type="button"
            class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-border/60 text-muted-foreground transition hover:text-foreground focus:outline-none focus:ring-2 focus:ring-primary/40"
            @click="cancelFilterNameModal()"
          >
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>
        <form class="mt-6 space-y-4" @submit.prevent="confirmFilterNameModal()">
          <label class="flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-muted-foreground">
            <span>Назва фільтру</span>
            <input
              type="text"
              class="rounded-2xl border border-input bg-background px-4 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
              x-model="filterNameModal.name"
              :placeholder="filterNameModal.defaultName || 'Новий фільтр'"
              x-ref="filterNameInput"
            />
          </label>
          <template x-if="filterNameModal.error">
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm text-rose-600" x-text="filterNameModal.error"></div>
          </template>
          <div class="flex flex-wrap items-center justify-end gap-2">
            <button
              type="button"
              class="inline-flex items-center gap-2 rounded-full border border-border/60 bg-background px-4 py-2 text-xs font-semibold text-muted-foreground transition hover:border-primary/40 hover:text-primary"
              @click.prevent="cancelFilterNameModal()"
            >
              Скасувати
            </button>
            <button
              type="submit"
              class="inline-flex items-center gap-2 rounded-full border border-primary/50 bg-primary/10 px-4 py-2 text-xs font-semibold text-primary transition hover:bg-primary/20"
            >
              Зберегти
            </button>
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
      x-show="contentManagement.tableSettings.open"
      x-cloak
      class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6"
      role="dialog"
      aria-modal="true"
    >
      <div
        class="absolute inset-0 bg-background/80 backdrop-blur-sm"
        @click="closeContentManagementTableSettings()"
      ></div>
      <div
        class="relative z-10 w-full max-w-3xl space-y-5 overflow-y-auto rounded-3xl border border-border/70 bg-card p-4 shadow-xl max-h-[calc(100vh-3rem)] sm:max-h-[calc(100vh-4rem)] sm:p-6 lg:max-h-[calc(100vh-6rem)]"
        @click.stop
      >
        <div class="flex flex-wrap items-start justify-between gap-4">
          <div class="space-y-2">
            <h2 class="text-lg font-semibold text-foreground">
              Налаштування таблиці
            </h2>
            <div class="text-sm text-muted-foreground">
              <div>
                Таблиця:
                <span class="font-semibold text-foreground" x-text="contentManagementLabel(contentManagement.tableSettings.table) || contentManagement.tableSettings.table"></span>
              </div>
              <p class="mt-1 text-sm text-muted-foreground">
                Задайте дружні назви для колонок та позначайте непотрібні поля як приховані. Порожні alias або колонки без назви не потраплять до конфігурації.
              </p>
            </div>
          </div>
          <button
            type="button"
            class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-border/60 text-muted-foreground transition hover:text-foreground focus:outline-none focus:ring-2 focus:ring-primary/40"
            @click="closeContentManagementTableSettings()"
            aria-label="Закрити налаштування таблиці"
          >
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>

        <template x-if="contentManagement.tableSettings.error">
          <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-600" x-text="contentManagement.tableSettings.error"></div>
        </template>

        <div class="max-h-[50vh] space-y-3 overflow-y-auto pr-1">
          <template x-if="contentManagement.tableSettings.entries.length === 0">
            <div class="rounded-2xl border border-dashed border-border/60 bg-muted/30 p-4 text-sm text-muted-foreground">
              Додайте колонку, щоб налаштувати для неї alias або приховати її у таблиці.
            </div>
          </template>
          <template x-for="(entry, entryIndex) in contentManagement.tableSettings.entries" :key="entry.id">
            <div class="flex flex-col gap-3 rounded-2xl border border-border/60 bg-background/70 p-4 sm:flex-row sm:items-end sm:gap-4">
              <label class="flex flex-1 flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-muted-foreground/80">
                <span>Колонка</span>
                <input
                  type="text"
                  class="rounded-xl border border-input bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40 disabled:cursor-not-allowed disabled:opacity-75"
                  placeholder="Наприклад, title"
                  x-model.trim="entry.column"
                  :readonly="entry.locked"
                  @change="updateContentManagementEntryRelation(entry)"
                />
                <span class="text-[11px] text-muted-foreground" x-show="entry.locked">Назва зі структури таблиці</span>
              </label>
              <div class="flex flex-1 flex-col gap-3">
                <label class="flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-muted-foreground/80">
                  <span>Alias</span>
                  <input
                    type="text"
                    class="rounded-xl border border-input bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
                    placeholder="Відображувана назва"
                    x-model="entry.alias"
                  />
                </label>
                <template x-if="entry.relation && entry.relation.table">
                  <div class="flex flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-muted-foreground/80">
                    <span>Поле пов'язаної таблиці</span>
                    <div>
                      <select
                        class="w-full rounded-xl border border-input bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40 disabled:cursor-not-allowed disabled:opacity-75"
                        x-model="entry.relation.displayColumn"
                        :disabled="entry.relation.options.length === 0"
                      >
                        <option value="">— Використати значення за замовчуванням —</option>
                        <template x-for="option in entry.relation.options" :key="option">
                          <option :value="option" x-text="option"></option>
                        </template>
                      </select>
                    </div>
                    <p class="text-[11px] font-normal normal-case text-muted-foreground">
                      <span>Зв'язок:</span>
                      <span class="font-semibold text-foreground" x-text="entry.relation.table"></span>
                      <template x-if="entry.relation.referencedColumn">
                        <span>
                          (<span x-text="entry.relation.referencedColumn"></span>)
                        </span>
                      </template>
                    </p>
                    <template x-if="entry.relation.options.length === 0">
                      <p class="text-[11px] font-normal normal-case text-amber-600">
                        Структура пов'язаної таблиці відсутня. Додайте її вручну у конфігурації або відкрийте таблицю зі структури.
                      </p>
                    </template>
                    <p class="text-[11px] font-normal normal-case text-muted-foreground/80">
                      Залиште порожнім, щоб використовувати значення за замовчуванням.
                    </p>
                  </div>
                </template>
              </div>
              <div class="flex items-start justify-between gap-3 sm:flex-col sm:items-end sm:gap-2">
                <div class="flex flex-col items-start gap-2 sm:items-end">
                  <div class="inline-flex items-center gap-2">
                    <button
                      type="button"
                      class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-border/60 bg-background text-muted-foreground transition hover:border-primary/60 hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary/40 disabled:cursor-not-allowed disabled:opacity-60"
                      :disabled="entryIndex === 0"
                      @click="moveContentManagementTableSettingsEntry(entryIndex, entryIndex - 1)"
                      aria-label="Перемістити колонку вгору"
                    >
                      <i class="fa-solid fa-arrow-up text-xs"></i>
                    </button>
                    <button
                      type="button"
                      class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-border/60 bg-background text-muted-foreground transition hover:border-primary/60 hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary/40 disabled:cursor-not-allowed disabled:opacity-60"
                      :disabled="entryIndex === contentManagement.tableSettings.entries.length - 1"
                      @click="moveContentManagementTableSettingsEntry(entryIndex, entryIndex + 1)"
                      aria-label="Перемістити колонку вниз"
                    >
                      <i class="fa-solid fa-arrow-down text-xs"></i>
                    </button>
                  </div>
                  <div class="flex items-center gap-2 sm:flex-row-reverse sm:justify-end sm:gap-3">
                    <label class="inline-flex items-center gap-2 text-[11px] font-semibold uppercase tracking-wide text-muted-foreground/80">
                      <input
                        type="checkbox"
                        class="h-4 w-4 rounded border-input text-primary focus:ring-primary/40"
                        x-model="entry.hidden"
                      />
                      <span>Приховати колонку</span>
                    </label>
                    <template x-if="entry.relation && entry.relation.additional">
                      <button
                        type="button"
                        class="inline-flex items-center justify-center gap-2 rounded-full border border-border/60 bg-background px-3 py-1 text-[11px] font-semibold uppercase tracking-wide text-muted-foreground transition hover:border-rose-300 hover:text-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-300/40"
                        @click="removeContentManagementTableSettingsEntry(entryIndex)"
                      >
                        <i class="fa-solid fa-trash-can text-xs"></i>
                        Видалити поле
                      </button>
                    </template>
                  </div>
                </div>
                <template x-if="!entry.locked">
                  <button
                    type="button"
                    class="inline-flex items-center justify-center gap-2 rounded-full border border-border/60 bg-background px-3 py-1.5 text-xs font-semibold text-muted-foreground transition hover:border-rose-300 hover:text-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-300/40 disabled:cursor-not-allowed disabled:opacity-60"
                    @click="removeContentManagementTableSettingsEntry(entryIndex)"
                  >
                    <i class="fa-solid fa-trash-can text-xs"></i>
                    Видалити
                  </button>
                </template>
              </div>
            </div>
          </template>
        </div>

        <div class="space-y-3">
          <div class="flex flex-wrap items-center justify-between gap-3">
            <button
              type="button"
              class="inline-flex items-center gap-2 rounded-full border border-border/60 bg-background px-4 py-1.5 text-xs font-semibold text-muted-foreground transition hover:border-primary/60 hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
              @click="addContentManagementTableSettingsEntry()"
            >
              <i class="fa-solid fa-plus text-[11px]"></i>
              Додати колонку
            </button>
            <div class="flex flex-col gap-1 text-xs text-muted-foreground sm:text-right">
              <span>Порожні alias будуть проігноровані при застосуванні та в JSON-конфігурації.</span>
            </div>
          </div>
          <template x-if="contentManagement.tableSettings.relationOptions.length > 0">
            <div class="flex flex-col gap-3 rounded-2xl border border-dashed border-border/60 bg-muted/20 p-3 sm:flex-row sm:items-end sm:gap-3">
              <label class="flex flex-1 flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-muted-foreground/80">
                <span>Поле зі зв'язаної таблиці</span>
                <select
                  class="rounded-xl border border-input bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
                  x-model="contentManagement.tableSettings.selectedRelationOption"
                >
                  <option value="">Оберіть поле</option>
                  <template x-for="group in contentManagement.tableSettings.relationOptions" :key="group.key">
                    <optgroup :label="group.label">
                      <template x-for="option in group.options" :key="option.key">
                        <option :value="option.key" x-text="option.label"></option>
                      </template>
                    </optgroup>
                  </template>
                </select>
              </label>
              <button
                type="button"
                class="inline-flex items-center justify-center gap-2 rounded-full border border-border/60 bg-background px-4 py-1.5 text-xs font-semibold text-muted-foreground transition hover:border-primary/60 hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary/40 disabled:cursor-not-allowed disabled:opacity-60"
                :disabled="!contentManagement.tableSettings.selectedRelationOption"
                @click="addContentManagementRelationColumn()"
              >
                <i class="fa-solid fa-plus text-[11px]"></i>
                Додати поле
              </button>
            </div>
          </template>
        </div>

        <div class="space-y-2">
          <label class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">JSON для config/database-structure.php</label>
          <textarea
            class="min-h-[120px] w-full rounded-2xl border border-input bg-background px-3 py-2 text-sm focus:outline-none"
            readonly
            :value="contentManagementTableSettingsSnippet()"
          ></textarea>
          <div class="flex flex-wrap items-center gap-3">
            <button
              type="button"
              class="inline-flex items-center gap-2 rounded-full border border-border/60 bg-background px-4 py-1.5 text-xs font-semibold text-muted-foreground transition hover:border-primary/60 hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary/40 disabled:cursor-not-allowed disabled:opacity-60"
              @click="copyContentManagementTableSettingsSnippet()"
              :disabled="!contentManagementTableSettingsSnippet()"
            >
              <i class="fa-solid fa-copy text-[11px]"></i>
              Скопіювати JSON
            </button>
            <template x-if="contentManagement.tableSettings.feedback">
              <span class="text-xs font-medium text-emerald-600" x-text="contentManagement.tableSettings.feedback"></span>
            </template>
          </div>
          <p class="text-[11px] text-muted-foreground">
            Додайте цей фрагмент до <code class="rounded bg-muted px-1">config/database-structure.php</code> у секцію <code class="rounded bg-muted px-1">content_management.table_settings</code>.
          </p>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
          <button
            type="button"
            class="inline-flex items-center justify-center gap-2 rounded-full border border-border/60 bg-background px-4 py-2 text-sm font-medium text-muted-foreground transition hover:border-primary/60 hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
            @click="closeContentManagementTableSettings()"
          >
            Скасувати
          </button>
          <button
            type="button"
            class="inline-flex items-center justify-center gap-2 rounded-full border border-primary bg-primary px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/40"
            @click="applyContentManagementTableSettings()"
          >
            Застосувати
          </button>
        </div>
      </div>
    </div>

    <div
      x-show="activeTab === 'content-management'"
      x-cloak
      class="grid gap-6 lg:grid-cols-[280px_1fr] lg:items-start"
    >
      <aside class="self-start space-y-4 rounded-3xl border border-border/70 bg-card/80 p-6 shadow-soft">
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
              Вийти
              </button>
              <button
                type="button"
                class="inline-flex items-center gap-2 rounded-full border border-primary bg-primary px-4 py-1.5 text-xs font-semibold text-white transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/40 disabled:cursor-not-allowed disabled:opacity-60"
                @click="addContentManagementMenuItem()"
                :disabled="contentManagement.menuSettings.saving"
              >
              <span x-show="!contentManagement.menuSettings.saving">Зберегти</span>
                <span x-show="contentManagement.menuSettings.saving" x-cloak>Збереження...</span>
              </button>
            </div>
        </div>

        <template x-if="contentManagement.menuFeedback">
          <div
            class="rounded-2xl border px-3 py-2 text-sm"
            :class="contentManagement.menuFeedbackType === 'success'
              ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
              : 'border-rose-200 bg-rose-50 text-rose-600'"
            x-text="contentManagement.menuFeedback"
          ></div>
        </template>

        <div class="space-y-2">
          <template x-if="contentManagement.menu.length === 0">
            <div class="rounded-2xl border border-dashed border-border/60 bg-muted/20 p-4 text-sm text-muted-foreground">
              Меню порожнє. Додайте таблицю через налаштування.
            </div>
          </template>
          <template x-for="(item, index) in contentManagement.menu" :key="`cm-item-${item.table}`">
            <div class="flex flex-wrap items-center gap-2">
              <button
                type="button"
                class="flex-1 rounded-2xl border px-4 py-3 text-left transition focus:outline-none focus:ring-2 focus:ring-primary/40"
                :class="contentManagement.selectedTable === item.table ? 'border-primary/70 bg-primary/10 text-primary' : 'border-border/60 bg-background text-foreground hover:border-primary/60 hover:text-primary'"
                @click="selectContentManagementTable(item.table)"
              >
                <div class="font-semibold" x-text="item.label || item.table"></div>
                <div class="text-xs text-muted-foreground" x-text="item.table"></div>
                <div
                  class="mt-2 inline-flex items-center gap-1 rounded-full bg-amber-100/80 px-2 py-0.5 text-[11px] font-semibold text-amber-700"
                  x-show="item.is_default"
                  x-cloak
                >
                  <i class="fa-solid fa-star text-[10px]"></i>
                  <span>За замовчуванням</span>
                </div>
              </button>
              <div
                class="inline-flex items-center gap-1"
                x-show="contentManagement.menuSettings.open"
                x-cloak
              >
                <button
                  type="button"
                  class="inline-flex items-center justify-center rounded-full border border-border/60 bg-background p-2 text-muted-foreground transition hover:border-amber-400 hover:bg-amber-50 hover:text-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-200/70 disabled:cursor-not-allowed disabled:opacity-60"
                  @click="setContentManagementDefaultTable(item.table)"
                  :disabled="contentManagement.menuSettings.saving || item.is_default"
                  :aria-label="item.is_default ? 'Таблиця вже обрана за замовчуванням' : 'Зробити таблицю за замовчуванням'"
                  :title="item.is_default ? 'Таблиця за замовчуванням' : 'Зробити таблицю за замовчуванням'"
                >
                  <i
                    class="fa-solid fa-star text-xs"
                    :class="item.is_default ? 'text-amber-500' : 'text-muted-foreground'"
                  ></i>
                </button>
                <button
                  type="button"
                  class="inline-flex items-center justify-center rounded-full border border-border/60 bg-background p-2 text-muted-foreground transition hover:border-primary/60 hover:bg-primary/10 hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary/40 disabled:cursor-not-allowed disabled:opacity-60"
                  @click="renameContentManagementMenuItem(item.table)"
                  :disabled="contentManagement.menuSettings.saving"
                  aria-label="Перейменувати таблицю в меню"
                >
                  <i class="fa-solid fa-pen text-xs"></i>
                </button>
                <button
                  type="button"
                  class="inline-flex items-center justify-center rounded-full border border-border/60 bg-background p-2 text-muted-foreground transition hover:border-primary/60 hover:bg-primary/10 hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary/40 disabled:cursor-not-allowed disabled:opacity-60"
                  @click="moveContentManagementMenuItem(item.table, -1)"
                  :disabled="contentManagement.menuSettings.saving || index === 0"
                  aria-label="Перемістити таблицю вгору"
                >
                  <i class="fa-solid fa-chevron-up text-xs"></i>
                </button>
                <button
                  type="button"
                  class="inline-flex items-center justify-center rounded-full border border-border/60 bg-background p-2 text-muted-foreground transition hover:border-primary/60 hover:bg-primary/10 hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary/40 disabled:cursor-not-allowed disabled:opacity-60"
                  @click="moveContentManagementMenuItem(item.table, 1)"
                  :disabled="contentManagement.menuSettings.saving || index === contentManagement.menu.length - 1"
                  aria-label="Перемістити таблицю вниз"
                >
                  <i class="fa-solid fa-chevron-down text-xs"></i>
                </button>
                <button
                  type="button"
                  class="inline-flex items-center justify-center rounded-full border border-border/60 bg-background p-2 text-muted-foreground transition hover:border-rose-300 hover:bg-rose-50 hover:text-rose-600 focus:outline-none focus:ring-2 focus:ring-rose-200/70 disabled:cursor-not-allowed disabled:opacity-60"
                  @click="openContentManagementDeletionModal(item)"
                  :disabled="(contentManagement.deletionModal.loading && contentManagement.deletionModal.table === item.table) || contentManagement.menuSettings.saving"
                  :aria-disabled="(contentManagement.deletionModal.loading && contentManagement.deletionModal.table === item.table) || contentManagement.menuSettings.saving"
                  aria-label="Видалити таблицю з меню"
                >
                  <i class="fa-solid fa-trash-can text-xs"></i>
                </button>
              </div>
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
                  <div class="mt-3 space-y-2">
                    <template x-if="contentManagement.viewer.feedback">
                      <span class="text-xs font-semibold text-emerald-600" x-text="contentManagement.viewer.feedback"></span>
                    </template>
                    <template x-if="contentManagement.viewer.savedFilters.loading">
                      <span class="text-[11px] text-muted-foreground">Завантаження збережених фільтрів…</span>
                    </template>
                    <template x-if="!contentManagement.viewer.savedFilters.loading && contentManagement.viewer.savedFilters.items.length > 0">
                      <div class="flex flex-wrap items-center gap-2 text-xs font-semibold">
                        <template x-for="saved in contentManagement.viewer.savedFilters.items" :key="`content-saved-${saved.id}`">
                          <div class="inline-flex items-center overflow-hidden rounded-full border border-border/70 bg-background">
                            <button
                              type="button"
                              class="px-3 py-1 transition hover:bg-primary/10 hover:text-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 disabled:cursor-not-allowed disabled:opacity-60"
                              :class="contentManagement.viewer.savedFilters.defaultId === saved.id ? 'bg-primary/10 text-primary' : 'text-foreground'"
                              :disabled="contentManagement.viewer.loading || contentManagement.viewer.savedFilters.loading"
                              @click.stop="applySavedContentManagementFilter(saved.id)"
                              x-text="saved.name"
                            ></button>
                            <button
                              type="button"
                              class="px-2 py-1 transition hover:text-amber-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-300 disabled:cursor-not-allowed disabled:opacity-60"
                              :class="contentManagement.viewer.savedFilters.defaultId === saved.id ? 'text-amber-500' : 'text-muted-foreground'"
                              :disabled="contentManagement.viewer.savedFilters.loading"
                              @click.stop="toggleContentManagementDefaultFilter(saved.id)"
                              :aria-label="contentManagement.viewer.savedFilters.defaultId === saved.id ? `Вимкнути фільтр ${saved.name} за замовчуванням` : `Зробити фільтр ${saved.name} за замовчуванням`"
                            >
                              <i class="fa-solid fa-star text-[10px]"></i>
                            </button>
                            <template x-if="contentManagementFilterShareUrl(saved)">
                              <a
                                class="px-2 py-1 text-muted-foreground transition hover:text-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
                                :href="contentManagementFilterShareUrl(saved)"
                                target="_blank"
                                rel="noopener"
                                :aria-label="`Відкрити таблицю з фільтром ${saved.name}`"
                              >
                                <i class="fa-solid fa-link text-[10px]"></i>
                              </a>
                            </template>
                            <button
                              type="button"
                              class="px-2 py-1 text-muted-foreground transition hover:text-rose-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-300 disabled:cursor-not-allowed disabled:opacity-60"
                              :disabled="contentManagement.viewer.savedFilters.loading"
                              @click.stop="deleteSavedContentManagementFilter(saved.id)"
                              :aria-label="`Видалити фільтр ${saved.name}`"
                            >
                              <i class="fa-solid fa-xmark text-[10px]"></i>
                            </button>
                          </div>
                        </template>
                        <button
                          type="button"
                          class="inline-flex items-center gap-2 rounded-full border border-border/70 bg-background px-3 py-1 text-[11px] font-semibold text-muted-foreground transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 disabled:cursor-not-allowed disabled:opacity-60"
                          :class="(!contentManagement.viewer.savedFilters.defaultId || contentManagement.viewer.savedFilters.defaultDisabled) ? 'border-primary/40 bg-primary/10 text-primary' : 'hover:border-primary/60 hover:text-primary'"
                          :aria-pressed="!contentManagement.viewer.savedFilters.defaultId || contentManagement.viewer.savedFilters.defaultDisabled"
                          :disabled="contentManagement.viewer.savedFilters.loading"
                          @click.stop="setContentManagementDefaultFilter('', { resetActive: true })"
                        >
                          <i class="fa-solid fa-ban text-[10px]"></i>
                          <span>All</span>
                        </button>
                      </div>
                    </template>
                  </div>
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
                  <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-full border border-border/60 bg-background px-4 py-1.5 text-xs font-semibold text-muted-foreground transition hover:border-primary/60 hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary/40 disabled:cursor-not-allowed disabled:opacity-60"
                    @click="copyContentManagementTableUrl(contentManagement.selectedTable)"
                    :disabled="!contentManagement.selectedTable || contentManagement.copyingUrl"
                  >
                    <i class="fa-solid fa-link text-[11px]"></i>
                    Скопіювати посилання
                  </button>
                  <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-full border border-border/60 bg-background px-4 py-1.5 text-xs font-semibold text-muted-foreground transition hover:border-primary/60 hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary/40 disabled:cursor-not-allowed disabled:opacity-60"
                    @click="openContentManagementTableSettingsModal()"
                    :disabled="!contentManagement.selectedTable || contentManagement.viewer.loading"
                  >
                    <i class="fa-solid fa-gear text-[11px]"></i>
                    Конфіг таблиці
                  </button>
                </div>
              </div>

            <div class="flex w-full flex-col gap-2 rounded-2xl border border-border/60 bg-background/70 p-4 text-xs font-semibold uppercase tracking-wide text-muted-foreground/80">
              <span class="font-semibold uppercase tracking-wide text-muted-foreground">Пошук записів</span>
              <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-3">
                <div class="relative flex-1">
                  <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-muted-foreground">
                    <i class="fa-solid fa-magnifying-glass text-[11px]"></i>
                  </span>
                  <input
                    type="search"
                    class="w-full rounded-xl border border-input bg-background py-2 pl-9 pr-4 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
                    placeholder="Миттєвий пошук..."
                    x-model="contentManagement.viewer.searchInput"
                    @input.debounce.400ms="updateContentManagementSearch($event.target.value)"
                    @keydown.enter.prevent
                  />
                </div>
                <label class="flex w-full flex-col gap-1 sm:w-56">
                  <span>Поле для пошуку</span>
                  <select
                    class="rounded-xl border border-input bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40 disabled:cursor-not-allowed disabled:opacity-75"
                    :disabled="contentManagement.viewer.loading || !Array.isArray(contentManagement.viewer.columns) || contentManagement.viewer.columns.length === 0"
                    :value="contentManagement.viewer.searchColumn"
                    @change="updateContentManagementSearchColumn($event.target.value)"
                  >
                    <option value="">Всі колонки</option>
                    <template x-for="column in contentManagement.viewer.columns" :key="`cm-search-${column}`">
                      <option :value="column" x-text="contentManagementColumnOptionLabel(column)"></option>
                    </template>
                  </select>
                </label>
              </div>
            </div>

            <div class="rounded-2xl border border-border/60 bg-muted/20 p-4 text-sm text-muted-foreground">
              <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <h3 class="text-sm font-semibold text-foreground">
                  <button
                    type="button"
                    class="inline-flex items-center gap-2 text-sm font-semibold text-foreground transition hover:text-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
                    @click.stop="toggleContentManagementFilters()"
                    :aria-expanded="contentManagement.viewer.filtersOpen ? 'true' : 'false'"
                    aria-controls="content-management-filters"
                  >
                    <i
                      class="fa-solid fa-chevron-down text-xs transition-transform duration-200"
                      :class="contentManagement.viewer.filtersOpen ? 'rotate-180 text-primary' : 'text-muted-foreground'"
                    ></i>
                    <span>Фільтри записів</span>
                  </button>
                </h3>
                <div
                  class="flex flex-col items-start gap-2 md:items-end"
                  x-show="contentManagement.viewer.filtersOpen"
                  x-transition
                  x-cloak
                >
                  <div class="flex flex-wrap items-center gap-2 text-sm">
                    <button
                      type="button"
                      class="inline-flex items-center gap-2 rounded-full border border-border/70 bg-background px-4 py-1.5 font-semibold text-foreground transition hover:border-primary/60 hover:text-primary disabled:cursor-not-allowed disabled:opacity-60"
                      @click="addContentManagementFilter()"
                      :disabled="contentManagement.viewer.loading || !contentManagement.selectedTable"
                    >
                      <i class="fa-solid fa-plus text-[10px]"></i>
                      Додати фільтр
                    </button>
                    <button
                      type="button"
                      class="inline-flex items-center gap-2 rounded-full border border-border/70 bg-background px-4 py-1.5 font-semibold text-foreground transition hover:border-primary/60 hover:text-primary disabled:cursor-not-allowed disabled:opacity-60"
                      :disabled="contentManagement.viewer.filters.length === 0 || contentManagement.viewer.loading"
                      @click="resetContentManagementFilters()"
                    >
                      <i class="fa-solid fa-rotate-left text-[10px]"></i>
                      Скинути
                    </button>
                    <button
                      type="button"
                      class="inline-flex items-center gap-2 rounded-full border border-primary/40 bg-primary/10 px-4 py-1.5 font-semibold text-primary transition hover:bg-primary/20 disabled:cursor-not-allowed disabled:opacity-60"
                      :disabled="contentManagement.viewer.loading || !contentManagement.selectedTable"
                      @click="saveContentManagementFilters()"
                    >
                      <i class="fa-solid fa-floppy-disk text-[10px]"></i>
                      Зберегти фільтр
                    </button>
                    <button
                      type="button"
                      class="inline-flex items-center gap-2 rounded-full border border-primary/40 bg-primary/10 px-4 py-1.5 font-semibold text-primary transition hover:bg-primary/20 disabled:cursor-not-allowed disabled:opacity-60"
                      :disabled="contentManagement.viewer.loading || !contentManagement.selectedTable"
                      @click="applyContentManagementFilters()"
                    >
                      <i class="fa-solid fa-filter text-[10px]"></i>
                      Застосувати
                    </button>
                  </div>
                </div>
              </div>

              <div
                x-show="contentManagement.viewer.filtersOpen"
                x-collapse
                x-cloak
                id="content-management-filters"
              >
                <p class="text-xs text-muted-foreground">
                  Використовуйте фільтри, щоб обмежити записи за значеннями колонок. Для операторів LIKE можна застосовувати символи
                  <code class="rounded bg-muted px-1">%</code> та <code class="rounded bg-muted px-1">_</code>.
                </p>

              <template x-if="contentManagement.viewer.filters.length === 0">
                <div class="mt-4 rounded-xl border border-dashed border-border/60 bg-background/60 p-4 text-sm text-muted-foreground">
                  Фільтри не задано. Додайте новий, щоб відфільтрувати записи.
                </div>
              </template>

              <div class="mt-4 space-y-3" x-show="contentManagement.viewer.filters.length > 0">
                <template x-for="(filter, filterIndex) in contentManagement.viewer.filters" :key="filter.id">
                  <div class="flex flex-col gap-3 rounded-xl border border-border/60 bg-background/70 p-4 sm:flex-row sm:items-end sm:gap-4">
                    <div class="flex flex-1 flex-col gap-3 sm:flex-row sm:items-end sm:gap-4">
                      <label class="flex flex-1 flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-muted-foreground/80">
                        <span>Поле</span>
                        <select
                          class="rounded-xl border border-input bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
                          x-model="filter.column"
                          :disabled="contentManagement.viewer.loading"
                        >
                          <option value="">Оберіть поле</option>
                          <template x-for="column in contentManagement.viewer.columns" :key="`${column}-content-filter`">
                            <option :value="column" x-text="contentManagementColumnOptionLabel(column)"></option>
                          </template>
                        </select>
                      </label>
                      <label class="flex w-full flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-muted-foreground/80 sm:w-48">
                        <span>Оператор</span>
                        <select
                          class="rounded-xl border border-input bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
                          x-model="filter.operator"
                          :disabled="contentManagement.viewer.loading"
                        >
                          <template x-for="option in filterOperators" :key="`content-filter-${option.value}`">
                            <option :value="option.value" x-text="option.label"></option>
                          </template>
                        </select>
                      </label>
                      <label
                        class="flex flex-1 flex-col gap-1 text-xs font-semibold uppercase tracking-wide text-muted-foreground/80"
                        x-show="operatorRequiresValue(filter.operator)"
                      >
                        <span>Значення</span>
                        <input
                          type="text"
                          class="rounded-xl border border-input bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
                          placeholder="Вкажіть значення"
                          x-model="filter.value"
                          :disabled="contentManagement.viewer.loading"
                        />
                      </label>
                    </div>
                    <button
                      type="button"
                      class="inline-flex items-center justify-center gap-2 rounded-full border border-border/70 bg-background px-3 py-1.5 text-sm font-semibold text-muted-foreground transition hover:border-rose-300 hover:text-rose-500 disabled:cursor-not-allowed disabled:opacity-60"
                      @click="removeContentManagementFilter(filterIndex)"
                      :disabled="contentManagement.viewer.loading"
                    >
                      <i class="fa-solid fa-trash-can text-xs"></i>
                      Видалити
                    </button>
                  </div>
                </template>
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
                <div class="-mx-6 w-full max-w-[calc(100vw-2rem)] overflow-x-auto sm:mx-0 sm:max-w-none sm:w-auto">
                  <table class="min-w-[40rem] divide-y divide-border/60 text-[15px] sm:min-w-full">
                    <thead class="text-left text-xs uppercase tracking-wider text-muted-foreground">
                      <tr>
                        <template x-for="column in contentManagement.viewer.columns" :key="`cm-column-${column}`">
                          <th class="px-3 py-2 font-medium">
                            <button
                              type="button"
                              class="flex w-full items-center gap-2 text-left text-xs font-semibold uppercase tracking-wider transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
                              :class="contentManagement.viewer.sort === column ? 'text-primary' : 'text-muted-foreground hover:text-primary'"
                              :title="column"
                              @click="toggleContentManagementSort(column)"
                            >
                              <span x-text="contentManagementColumnHeading(column)"></span>
                              <span class="text-[10px]" x-show="contentManagement.viewer.sort === column" x-cloak>
                                <i
                                  class="fa-solid"
                                  :class="contentManagement.viewer.direction === 'asc' ? 'fa-arrow-up-short-wide' : 'fa-arrow-down-wide-short'"
                                ></i>
                              </span>
                            </button>
                          </th>
                        </template>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-border/60 text-[15px] text-foreground">
                      <template x-for="(row, rowIndex) in contentManagement.viewer.rows" :key="`cm-row-${rowIndex}`">
                        <tr class="hover:bg-muted/40 transition">
                          <template x-for="column in contentManagement.viewer.columns" :key="`cm-cell-${rowIndex}-${column}`">
                            <td class="px-3 py-2 align-top">
                              <button
                                type="button"
                                class="-mx-2 -my-1 block w-full rounded-lg px-2 py-1 text-left text-sm text-foreground transition hover:bg-primary/5 focus:outline-none focus:ring-2 focus:ring-primary/40"
                                @click.stop="showContentManagementRecordValue(column, row)"
                                :title="formatCell(contentManagementCellValue(row, column))"
                                x-html="contentManagementHighlight(column, row)"
                              ></button>
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
      contentManagementSettings = {},
      filterRoutes = {},
      keywordSearchRoute = '',
      viewOptions = {},
    ) {
      const normalizeFilterScope = (scope) => (scope === 'content' ? 'content' : 'records');

      const normalizeFilterRoutes = (routes) => {
        const source = routes && typeof routes === 'object' ? routes : {};

        return {
          index: typeof source.index === 'string' ? source.index : '',
          store: typeof source.store === 'string' ? source.store : '',
          destroy: typeof source.destroy === 'string' ? source.destroy : '',
          use: typeof source.use === 'string' ? source.use : '',
          default: typeof source.default === 'string' ? source.default : '',
        };
      };

      const buildFilterRoute = (template, tableName, scope, filterId = null) => {
        if (typeof template !== 'string' || template.trim() === '') {
          return '';
        }

        const normalizedTable = typeof tableName === 'string' ? tableName.trim() : '';
        const normalizedScope = normalizeFilterScope(scope);

        if (!normalizedTable) {
          return '';
        }

        let route = template.replace('__TABLE__', encodeURIComponent(normalizedTable));
        route = route.replace('__SCOPE__', encodeURIComponent(normalizedScope));

        if (route.includes('__FILTER__')) {
          if (filterId === null) {
            return '';
          }

          const normalizedFilter = typeof filterId === 'string' ? filterId.trim() : '';

          if (!normalizedFilter) {
            return '';
          }

          route = route.replace('__FILTER__', encodeURIComponent(normalizedFilter));
        }

        return route;
      };

      const normalizeSavedFilterEntry = (entry) => {
        if (!entry || typeof entry !== 'object') {
          return null;
        }

        const id = typeof entry.id === 'string' ? entry.id.trim() : '';
        const name = typeof entry.name === 'string' ? entry.name.trim() : '';

        if (!id || !name) {
          return null;
        }

        const filtersSource = Array.isArray(entry.filters) ? entry.filters : [];
        const filters = filtersSource
          .map((filter) => {
            if (!filter || typeof filter !== 'object') {
              return null;
            }

            const column = typeof filter.column === 'string' ? filter.column.trim() : '';
            const operator = typeof filter.operator === 'string' ? filter.operator.trim() : '';
            const rawValue = filter.value === undefined || filter.value === null ? '' : String(filter.value);

            if (!column || !operator) {
              return null;
            }

            return {
              column,
              operator,
              value: rawValue,
            };
          })
          .filter(Boolean);

        const search = typeof entry.search === 'string' ? entry.search : '';
        const searchColumn = typeof entry.search_column === 'string'
          ? entry.search_column.trim()
          : '';

        return {
          id,
          name,
          filters,
          search,
          searchColumn,
        };
      };

      const normalizeSavedFiltersResponse = (payload) => {
        const itemsSource = payload && typeof payload === 'object' ? payload.filters : [];
        const lastUsed = payload && typeof payload === 'object' && typeof payload.last_used === 'string'
          ? payload.last_used.trim()
          : '';
        const defaultId = payload && typeof payload === 'object' && typeof payload.default === 'string'
          ? payload.default.trim()
          : '';
        let defaultDisabled = false;

        if (payload && typeof payload === 'object') {
          if (typeof payload.default_disabled === 'boolean') {
            defaultDisabled = payload.default_disabled;
          } else if (payload.default_disabled === 1 || payload.default_disabled === '1') {
            defaultDisabled = true;
          }
        }

        const items = Array.isArray(itemsSource)
          ? itemsSource.map((entry) => normalizeSavedFilterEntry(entry)).filter(Boolean)
          : [];

        return {
          items,
          lastUsed,
          defaultId,
          defaultDisabled,
        };
      };

      const createSavedFiltersState = () => ({
        loading: false,
        loaded: false,
        items: [],
        lastUsed: '',
        defaultId: '',
        defaultDisabled: false,
      });

      const hasActiveRecordsFilters = (state) => {
        if (!state || typeof state !== 'object') {
          return false;
        }

        const hasFilters = Array.isArray(state.filters) && state.filters.length > 0;
        const hasSearch = typeof state.search === 'string' && state.search.trim() !== '';

        return hasFilters || hasSearch;
      };

      const hasActiveContentFilters = (viewer) => {
        if (!viewer || typeof viewer !== 'object') {
          return false;
        }

        const hasFilters = Array.isArray(viewer.filters) && viewer.filters.length > 0;
        const hasSearch = typeof viewer.search === 'string' && viewer.search.trim() !== '';

        return hasFilters || hasSearch;
      };

      const serializeFilters = (filters) => {
        if (!Array.isArray(filters)) {
          return [];
        }

        return filters
          .map((filter) => {
            if (!filter || typeof filter !== 'object') {
              return null;
            }

            const column = typeof filter.column === 'string' ? filter.column.trim() : '';
            const operator = typeof filter.operator === 'string' ? filter.operator.trim() : '';
            const rawValue = filter.value;
            const value = rawValue === undefined || rawValue === null ? '' : String(rawValue);

            if (!column || !operator) {
              return null;
            }

            return {
              column,
              operator,
              value,
            };
          })
          .filter(Boolean);
      };

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

        const coerceBoolean = (value) => {
          if (typeof value === 'boolean') {
            return value;
          }

          if (typeof value === 'string') {
            const normalized = value.trim().toLowerCase();

            return ['1', 'true', 'yes', 'on'].includes(normalized);
          }

          if (typeof value === 'number') {
            return Number.isFinite(value) && value !== 0;
          }

          return false;
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
              is_default: false,
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
            is_default: coerceBoolean(item.is_default ?? item.default ?? false),
          };
        };

        const normalizeContentManagementMenu = (rawMenu) => {
          if (!Array.isArray(rawMenu)) {
            return [];
          }

          const seen = new Set();
          let defaultAssigned = false;

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
            })
            .map((item) => {
              if (!item) {
                return item;
              }

              if (item.is_default && !defaultAssigned) {
                defaultAssigned = true;
              } else {
                item.is_default = false;
              }

              return item;
            });
        };

        const normalizeContentManagementRoutes = (routes) => {
          const normalized = routes && typeof routes === 'object' ? routes : {};

          return {
            menuStore: typeof normalized.menuStore === 'string' ? normalized.menuStore : '',
            menuUpdate: typeof normalized.menuUpdate === 'string' ? normalized.menuUpdate : '',
            menuDelete: typeof normalized.menuDelete === 'string' ? normalized.menuDelete : '',
          };
        };

        const normalizeAliasMap = (source) => {
          const result = {};

          if (!source) {
            return result;
          }

          if (Array.isArray(source)) {
            source.forEach((entry) => {
              if (!entry) {
                return;
              }

              if (Array.isArray(entry)) {
                if (entry.length < 2) {
                  return;
                }

                const [rawColumn, rawAlias] = entry;
                const columnName = typeof rawColumn === 'string' ? rawColumn.trim() : '';
                const aliasName = typeof rawAlias === 'string' ? rawAlias.trim() : '';

                if (columnName && aliasName) {
                  result[columnName] = aliasName;
                }

                return;
              }

              if (typeof entry === 'object') {
                const columnName = typeof entry.column === 'string' ? entry.column.trim() : '';
                let aliasName = '';

                if (typeof entry.alias === 'string') {
                  aliasName = entry.alias.trim();
                } else if (typeof entry.label === 'string') {
                  aliasName = entry.label.trim();
                } else if (typeof entry.title === 'string') {
                  aliasName = entry.title.trim();
                }

                if (columnName && aliasName) {
                  result[columnName] = aliasName;
                }
              }
            });

            return result;
          }

          if (typeof source === 'object') {
            Object.entries(source).forEach(([rawColumn, rawAlias]) => {
              const columnName = typeof rawColumn === 'string' ? rawColumn.trim() : '';

              if (!columnName) {
                return;
              }

              let aliasName = '';

              if (typeof rawAlias === 'string') {
                aliasName = rawAlias.trim();
              } else if (rawAlias && typeof rawAlias === 'object') {
                if (typeof rawAlias.alias === 'string') {
                  aliasName = rawAlias.alias.trim();
                } else if (typeof rawAlias.label === 'string') {
                  aliasName = rawAlias.label.trim();
                } else if (typeof rawAlias.title === 'string') {
                  aliasName = rawAlias.title.trim();
                }
              }

              if (aliasName) {
                result[columnName] = aliasName;
              }
            });
          }

          return result;
        };

        const normalizeHiddenColumnsList = (source) => {
          if (!source) {
            return [];
          }

          const set = new Set();

          const push = (value) => {
            if (typeof value !== 'string') {
              return;
            }

            const normalized = value.trim();

            if (normalized) {
              set.add(normalized);
            }
          };

          if (Array.isArray(source)) {
            source.forEach((entry) => {
              if (typeof entry === 'string') {
                push(entry);
                return;
              }

              if (entry && typeof entry === 'object') {
                Object.entries(entry).forEach(([key, value]) => {
                  const columnName = typeof key === 'string' ? key.trim() : '';
                  const normalizedValue = typeof value === 'string' ? value.trim().toLowerCase() : value;

                  if (normalizedValue === true || normalizedValue === 'true' || normalizedValue === '1' || normalizedValue === 1) {
                    if (columnName) {
                      set.add(columnName);
                    }
                  }
                });
              }
            });
          } else if (typeof source === 'string') {
            push(source);
          } else if (source && typeof source === 'object') {
            Object.entries(source).forEach(([key, value]) => {
              const columnName = typeof key === 'string' ? key.trim() : '';

              if (!columnName) {
                if (typeof value === 'string') {
                  push(value);
                }

                return;
              }

              if (value === true || value === 1 || value === '1' || value === 'true') {
                set.add(columnName);
                return;
              }

              if (Array.isArray(value)) {
                normalizeHiddenColumnsList(value).forEach((entry) => set.add(entry));
                return;
              }

              if (typeof value === 'string') {
                const normalizedValue = value.trim().toLowerCase();

                if (normalizedValue === 'true' || normalizedValue === '1') {
                  set.add(columnName);
                }
              }
            });
          }

          return Array.from(set);
        };

        const normalizeColumnOrderList = (source) => {
          if (!source) {
            return [];
          }

          const result = [];
          const seen = new Set();

          const push = (value) => {
            if (typeof value !== 'string') {
              return;
            }

            const normalized = value.trim();

            if (normalized && !seen.has(normalized)) {
              seen.add(normalized);
              result.push(normalized);
            }
          };

          if (Array.isArray(source)) {
            source.forEach((entry) => {
              if (typeof entry === 'string') {
                push(entry);
                return;
              }

              if (Array.isArray(entry) && entry.length > 0) {
                push(entry[0]);
                return;
              }

              if (entry && typeof entry === 'object') {
                if (typeof entry.column === 'string') {
                  push(entry.column);
                  return;
                }

                if (typeof entry.name === 'string') {
                  push(entry.name);
                }
              }
            });
          } else if (typeof source === 'string') {
            push(source);
          }

          return result;
        };

        const normalizeRelationDisplayMap = (source, allowSimple = false) => {
          const result = {};

          const push = (columnName, definition) => {
            const column = typeof columnName === 'string' ? columnName.trim() : '';

            if (!column) {
              return;
            }

            let table = '';
            let display = '';
            let sourceColumn = '';

            const segments = column.includes('::') ? column.split('::') : [];
            const keySource = segments.length > 0 && typeof segments[0] === 'string' ? segments[0].trim() : '';
            const keyTable = segments.length > 1 && typeof segments[1] === 'string' ? segments[1].trim() : '';
            const keyDisplay = segments.length > 2 && typeof segments[2] === 'string' ? segments[2].trim() : '';

            if (typeof definition === 'string') {
              const value = definition.trim();

              if (!value) {
                return;
              }

              if (value.includes('.')) {
                const [rawTable, rawColumn] = value.split('.', 2);
                table = typeof rawTable === 'string' ? rawTable.trim() : '';
                display = typeof rawColumn === 'string' ? rawColumn.trim() : '';
              } else if (allowSimple) {
                display = value;
              } else {
                return;
              }
            } else if (definition && typeof definition === 'object') {
              const tableCandidate = definition.table
                ?? definition.target_table
                ?? definition.targetTable
                ?? definition.foreign_table
                ?? definition.foreignTable
                ?? definition.relation_table
                ?? definition.relationTable;

              if (typeof tableCandidate === 'string') {
                table = tableCandidate.trim();
              }

              const displayCandidate = definition.display
                ?? definition.display_column
                ?? definition.displayColumn
                ?? definition.column
                ?? definition.field
                ?? definition.name
                ?? definition.value;

              if (typeof displayCandidate === 'string') {
                display = displayCandidate.trim();
              }

              if (!display && typeof definition.path === 'string' && definition.path.includes('.')) {
                const [rawTable, rawColumn] = definition.path.split('.', 2);

                if (!display && typeof rawColumn === 'string') {
                  display = rawColumn.trim();
                }

                if (!table && typeof rawTable === 'string') {
                  table = rawTable.trim();
                }
              }

              const sourceCandidate = definition.source
                ?? definition.source_column
                ?? definition.sourceColumn
                ?? definition.foreign_key
                ?? definition.foreignKey;

              if (typeof sourceCandidate === 'string') {
                sourceColumn = sourceCandidate.trim();
              }
            } else {
              return;
            }

            if (!display && keyDisplay) {
              display = keyDisplay;
            }

            if (!display) {
              return;
            }

            if (!table && keyTable) {
              table = keyTable;
            }

            if (table === '__self__') {
              table = '';
            }

            if (!sourceColumn && keySource) {
              sourceColumn = keySource;
            }

            const payload = { column: display };

            if (table) {
              payload.table = table;
            }

            if (sourceColumn) {
              payload.source = sourceColumn;
            }

            result[column] = payload;
          };

          if (!source) {
            return result;
          }

          if (Array.isArray(source)) {
            source.forEach((entry) => {
              if (!entry) {
                return;
              }

              if (Array.isArray(entry)) {
                if (entry.length < 2) {
                  return;
                }

                const [columnName, value] = entry;
                push(columnName, value);
                return;
              }

              if (typeof entry === 'object') {
                const columnName = entry.column
                  ?? entry.field
                  ?? entry.source
                  ?? entry.name;

                push(columnName, entry);
              }
            });

            return result;
          }

          if (typeof source === 'object') {
            Object.entries(source).forEach(([columnName, value]) => {
              push(columnName, value);
            });
          }

          return result;
        };

        const normalizeContentManagementTableSettings = (settings) => {
          if (!settings || typeof settings !== 'object') {
            return {};
          }

          const normalized = {};

          Object.entries(settings).forEach(([rawTable, rawConfig]) => {
            const tableName = typeof rawTable === 'string' ? rawTable.trim() : '';

            if (!tableName) {
              return;
            }

            let aliasMap = {};
            let hiddenColumns = [];
            let relationMap = {};
            let columnOrder = [];

            if (Array.isArray(rawConfig)) {
              aliasMap = normalizeAliasMap(rawConfig);

              if (Object.keys(aliasMap).length === 0) {
                hiddenColumns = normalizeHiddenColumnsList(rawConfig);
              }
            } else if (rawConfig && typeof rawConfig === 'object') {
              const candidates = [
                rawConfig.aliases,
                rawConfig.columns,
                rawConfig.fields,
                rawConfig.column_aliases,
                rawConfig.columnAliases,
              ];

              aliasMap = candidates.reduce((carry, candidate) => {
                if (Object.keys(carry).length > 0) {
                  return carry;
                }

                const normalizedCandidate = normalizeAliasMap(candidate);

                if (Object.keys(normalizedCandidate).length > 0) {
                  return normalizedCandidate;
                }

                return carry;
              }, {});

              if (Object.keys(aliasMap).length === 0) {
                aliasMap = normalizeAliasMap(rawConfig);
              }

              const hiddenCandidates = [
                rawConfig.hidden,
                rawConfig.hidden_columns,
                rawConfig.hiddenColumns,
                rawConfig.columns_hidden,
                rawConfig.columnsHidden,
                rawConfig.hide,
              ];

              hiddenColumns = hiddenCandidates.reduce((carry, candidate) => {
                if (carry.length > 0) {
                  return carry;
                }

                const normalizedHidden = normalizeHiddenColumnsList(candidate);

                if (normalizedHidden.length > 0) {
                  return normalizedHidden;
                }

                return carry;
              }, []);

              const relationCandidates = [
                rawConfig.relations,
                rawConfig.relation_columns,
                rawConfig.relationColumns,
                rawConfig.foreign_relations,
                rawConfig.foreignRelations,
                rawConfig.display_relations,
                rawConfig.displayRelations,
              ];

              relationMap = relationCandidates.reduce((carry, candidate) => {
                const normalized = normalizeRelationDisplayMap(candidate, true);

                if (Object.keys(normalized).length === 0) {
                  return carry;
                }

                return { ...carry, ...normalized };
              }, {});

              const orderCandidates = [
                rawConfig.order,
                rawConfig.column_order,
                rawConfig.columnOrder,
                rawConfig.columns_order,
                rawConfig.columnsOrder,
                rawConfig.order_columns,
                rawConfig.orderColumns,
              ];

              columnOrder = orderCandidates.reduce((carry, candidate) => {
                if (carry.length > 0) {
                  return carry;
                }

                const normalizedOrder = normalizeColumnOrderList(candidate);

                if (normalizedOrder.length > 0) {
                  return normalizedOrder;
                }

                return carry;
              }, []);
            }

            if (hiddenColumns.length === 0) {
              hiddenColumns = normalizeHiddenColumnsList(rawConfig);
            }

            if (Object.keys(relationMap).length === 0) {
              relationMap = normalizeRelationDisplayMap(rawConfig, false);
            }

            if (columnOrder.length === 0) {
              columnOrder = normalizeColumnOrderList(rawConfig);
            }

            const hasAliases = Object.keys(aliasMap).length > 0;
            const hasHidden = hiddenColumns.length > 0;

            const hasRelations = Object.keys(relationMap).length > 0;
            const hasOrder = columnOrder.length > 0;

            if (!hasAliases && !hasHidden && !hasRelations && !hasOrder) {
              return;
            }

            normalized[tableName] = {
              ...(hasAliases ? { aliases: aliasMap } : {}),
              ...(hasHidden ? { hidden: hiddenColumns } : {}),
              ...(hasRelations ? { relations: relationMap } : {}),
              ...(hasOrder ? { order: columnOrder } : {}),
            };
          });

          return normalized;
        };

        const createContentManagementViewerState = () => ({
          table: '',
          loading: false,
          error: null,
          columns: [],
          rows: [],
          sort: '',
          direction: 'asc',
          filters: [],
          filtersOpen: false,
          search: '',
          searchInput: '',
          searchColumn: '',
          page: 1,
          perPage: 20,
          total: 0,
          lastPage: 1,
          requestId: 0,
          loaded: false,
          feedback: '',
          feedbackTimeout: null,
          restoredFromStorage: false,
          savedFilters: createSavedFiltersState(),
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
                filtersOpen: false,
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
                feedback: '',
                feedbackTimeout: null,
                restoredFromStorage: false,
                savedFilters: createSavedFiltersState(),
              },
            };
          })
          .filter(Boolean);

        const normalizedContentManagementMenu = normalizeContentManagementMenu(contentManagementMenu);
        const normalizedContentManagementRoutes = normalizeContentManagementRoutes(contentManagementRoutes);
        const normalizedContentManagementSettings = normalizeContentManagementTableSettings(contentManagementSettings);
        const normalizedFilterRoutes = normalizeFilterRoutes(filterRoutes);
        const normalizedViewOptions =
          viewOptions && typeof viewOptions === 'object' && !Array.isArray(viewOptions)
            ? viewOptions
            : {};
        const resolveDefaultContentManagementTable = (menu) => {
          if (!Array.isArray(menu)) {
            return '';
          }

          const found = menu.find((item) => item && typeof item.table === 'string' && item.is_default);

          return found ? found.table : '';
        };
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
          filterRoutes: normalizedFilterRoutes,
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
            defaultTable: resolveDefaultContentManagementTable(normalizedContentManagementMenu),
            settings: normalizedContentManagementSettings,
            menuFeedback: '',
            menuFeedbackType: 'success',
            menuFeedbackTimeout: null,
            menuSettings: {
              open: false,
              table: '',
              label: '',
              saving: false,
              error: null,
            },
            copyingUrl: false,
            tableSettings: {
              open: false,
              table: '',
              entries: [],
              error: null,
              feedback: '',
              nextId: 0,
              relationOptions: [],
              selectedRelationOption: '',
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
            pendingFilterId: '',
            preventAutoSelect: false,
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
          filterNameModal: {
            open: false,
            name: '',
            defaultName: '',
            error: '',
            resolve: null,
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
            canEdit: true,
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
          keywordSearchRoute: typeof keywordSearchRoute === 'string' ? keywordSearchRoute : '',
          globalSearch: {
            keyword: '',
            loading: false,
            error: '',
            results: [],
            completed: false,
            limit: 5,
            requestId: 0,
          },
          tables: normalizedTables,
          init() {
            this.syncBodyScrollLock();

            this.$watch('valueModal.open', () => {
              this.syncBodyScrollLock();
            });

            this.$watch('manualForeignModal.open', () => {
              this.syncBodyScrollLock();
            });

            this.$watch('filterNameModal.open', (open) => {
              this.syncBodyScrollLock();

              if (open) {
                this.$nextTick(() => {
                  const input = this.$refs?.filterNameInput;

                  if (input && typeof input.focus === 'function') {
                    input.focus();

                    if (typeof input.select === 'function') {
                      input.select();
                    }
                  }
                });
              }
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

            this.$watch('contentManagement.tableSettings.open', () => {
              this.syncBodyScrollLock();
            });

            this.$watch('contentManagement.menu', (menu) => {
              const normalizedMenu = Array.isArray(menu) ? menu : [];
              const defaultItem = normalizedMenu.find((item) => item && item.is_default);

              this.contentManagement.defaultTable = defaultItem && defaultItem.table ? defaultItem.table : '';

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
                !this.contentManagement.preventAutoSelect &&
                normalizedMenu.length > 0
              ) {
                const targetItem = defaultItem || normalizedMenu[0];

                if (targetItem && targetItem.table) {
                  this.selectContentManagementTable(targetItem.table);
                }
              }
            });

            const searchParams = new URLSearchParams(window.location.search || '');
            const rawContentTableParam = searchParams.get('table') ?? searchParams.get('content_table');
            const rawContentFilterParam = searchParams.get('content_filter');
            const urlContentFilter = typeof rawContentFilterParam === 'string'
              ? rawContentFilterParam.trim()
              : '';

            if (urlContentFilter) {
              this.contentManagement.pendingFilterId = urlContentFilter;
            }

            const urlContentTable = typeof rawContentTableParam === 'string'
              ? rawContentTableParam.trim()
              : '';

            if (
              urlContentTable &&
              (this.standaloneTab === 'content-management' || this.activeTab === 'content-management')
            ) {
              this.contentManagement.preventAutoSelect = true;

              this.$nextTick(() => {
                this.selectContentManagementTable(urlContentTable, {
                  filterId: urlContentFilter,
                  markAsUsed: true,
                }).finally(() => {
                  this.contentManagement.preventAutoSelect = false;
                });
              });
            }

            if (
              this.activeTab === 'content-management' &&
              !this.contentManagement.selectedTable &&
              !this.contentManagement.preventAutoSelect &&
              Array.isArray(this.contentManagement.menu) &&
              this.contentManagement.menu.length > 0
            ) {
              const defaultItem = this.contentManagement.menu.find((item) => item && item.is_default);
              const targetItem = defaultItem || this.contentManagement.menu[0];

              if (targetItem && targetItem.table) {
                this.selectContentManagementTable(targetItem.table);
              }
            }
          },
          buildFilterUrl(type, tableName, scope, filterId = null) {
            const normalizedTable = typeof tableName === 'string' ? tableName.trim() : '';

            if (!normalizedTable) {
              return '';
            }

            const routes = this.filterRoutes ?? {};
            const template = routes?.[type];

            if (typeof template !== 'string' || template.trim() === '') {
              return '';
            }

            const normalizedScope = normalizeFilterScope(scope);

            return buildFilterRoute(template, normalizedTable, normalizedScope, filterId);
          },
          async sendFilterRequest(method, routeKey, scope, tableName, options = {}) {
            const normalizedScope = normalizeFilterScope(scope);
            const urlPath = this.buildFilterUrl(routeKey, tableName, normalizedScope, options.filterId ?? null);

            if (!urlPath) {
              throw new Error('Маршрут для роботи з фільтрами не налаштовано.');
            }

            const url = new URL(urlPath, window.location.origin);
            const fetchOptions = {
              method,
              headers: {
                Accept: 'application/json',
              },
            };

            if (options.body) {
              fetchOptions.headers['Content-Type'] = 'application/json';
              fetchOptions.headers['X-CSRF-TOKEN'] = this.csrfToken || '';
              fetchOptions.body = JSON.stringify(options.body);
            } else if (method !== 'GET') {
              fetchOptions.headers['X-CSRF-TOKEN'] = this.csrfToken || '';
            }

            const response = await fetch(url.toString(), fetchOptions);

            if (!response.ok) {
              const payload = await response.json().catch(() => null);
              const message = payload?.message || 'Не вдалося виконати запит для фільтрів.';
              throw new Error(message);
            }

            return response.json().catch(() => ({}));
          },
          async fetchSavedFiltersFor(scope, tableName, targetState, { force = false } = {}) {
            if (!targetState) {
              return { items: [], lastUsed: '', defaultId: '', defaultDisabled: false };
            }

            const normalizedTable = typeof tableName === 'string' ? tableName.trim() : '';

            if (!normalizedTable) {
              targetState.items = [];
              targetState.lastUsed = '';
              targetState.defaultId = '';
              targetState.defaultDisabled = false;
              targetState.loaded = true;
              return { items: [], lastUsed: '', defaultId: '', defaultDisabled: false };
            }

            if (!force && targetState.loaded) {
              return {
                items: targetState.items,
                lastUsed: targetState.lastUsed,
                defaultId: targetState.defaultId,
                defaultDisabled: targetState.defaultDisabled === true,
              };
            }

            if (!this.filterRoutes.index) {
              targetState.items = [];
              targetState.lastUsed = '';
              targetState.defaultId = '';
              targetState.defaultDisabled = false;
              targetState.loaded = true;
              return { items: [], lastUsed: '', defaultId: '', defaultDisabled: false };
            }

            targetState.loading = true;

            try {
              const response = await this.sendFilterRequest('GET', 'index', scope, normalizedTable);
              const normalized = normalizeSavedFiltersResponse(response || {});
              this.updateSavedFiltersState(targetState, normalized);
              return normalized;
            } catch (error) {
              targetState.items = [];
              targetState.lastUsed = '';
              targetState.defaultId = '';
              targetState.defaultDisabled = false;
              targetState.loaded = true;
              return { items: [], lastUsed: '', defaultId: '', defaultDisabled: false };
            } finally {
              targetState.loading = false;
            }
          },
          updateSavedFiltersState(targetState, normalized) {
            if (!targetState) {
              return;
            }

          targetState.items = Array.isArray(normalized.items) ? normalized.items : [];
          targetState.lastUsed = typeof normalized.lastUsed === 'string' ? normalized.lastUsed : '';
          targetState.defaultId = typeof normalized.defaultId === 'string' ? normalized.defaultId : '';
          targetState.defaultDisabled = normalized.defaultDisabled === true;
          targetState.loaded = true;
        },
        async updateSavedFilterDefault(scope, tableName, filterId, state, feedbackTarget) {
          if (!state) {
            return;
          }

          const normalizedScope = normalizeFilterScope(scope);
          const normalizedTable = typeof tableName === 'string' ? tableName.trim() : '';
          const normalizedFilterId = typeof filterId === 'string' ? filterId.trim() : '';

          if (!normalizedTable) {
            if (feedbackTarget) {
              this.setFeedback(feedbackTarget, 'Оберіть таблицю, щоб оновити фільтр за замовчуванням.');
            }
            return;
          }

          if (!this.filterRoutes.default) {
            if (feedbackTarget) {
              this.setFeedback(feedbackTarget, 'Оновлення фільтру за замовчуванням недоступне.');
            }
            return;
          }

          state.loading = true;

          if (feedbackTarget) {
            this.clearFeedback(feedbackTarget);
          }

          try {
            const response = await this.sendFilterRequest('PATCH', 'default', normalizedScope, normalizedTable, {
              body: {
                filter_id: normalizedFilterId !== '' ? normalizedFilterId : null,
              },
            });
            const normalized = normalizeSavedFiltersResponse(response || {});
            this.updateSavedFiltersState(state, normalized);

            if (feedbackTarget) {
              if (normalized.defaultId) {
                this.setFeedback(feedbackTarget, 'Фільтр за замовчуванням оновлено.');
              } else {
                this.setFeedback(feedbackTarget, 'Фільтр за замовчуванням вимкнено.');
              }
            }
          } catch (error) {
            if (feedbackTarget) {
              this.setFeedback(
                feedbackTarget,
                error?.message ?? 'Не вдалося оновити фільтр за замовчуванням.',
              );
            }
          } finally {
            state.loading = false;
          }
        },
          generateDefaultFilterName(existing) {
            const items = Array.isArray(existing) ? existing : [];
            const base = 'Фільтр';
            const existingNames = new Set(
              items
                .map((item) => (item && typeof item.name === 'string' ? item.name.toLowerCase() : ''))
                .filter((name) => name !== ''),
            );

            for (let index = 1; index <= items.length + 10; index += 1) {
              const candidate = `${base} ${index}`;

              if (!existingNames.has(candidate.toLowerCase())) {
                return candidate;
              }
            }

            return `${base} ${Date.now()}`;
          },
          promptFilterName(defaultName = '') {
            const fallback = typeof defaultName === 'string' && defaultName.trim() !== ''
              ? defaultName.trim()
              : 'Новий фільтр';

            return new Promise((resolve) => {
              if (typeof this.filterNameModal.resolve === 'function') {
                this.filterNameModal.resolve(null);
              }

              this.filterNameModal.open = true;
              this.filterNameModal.name = fallback;
              this.filterNameModal.defaultName = fallback;
              this.filterNameModal.error = '';
              this.filterNameModal.resolve = resolve;

              this.$nextTick(() => {
                const input = this.$refs?.filterNameInput;

                if (input && typeof input.focus === 'function') {
                  input.focus();

                  if (typeof input.select === 'function') {
                    input.select();
                  }
                }
              });
            });
          },
          closeFilterNameModal() {
            this.filterNameModal.open = false;
            this.filterNameModal.name = '';
            this.filterNameModal.defaultName = '';
            this.filterNameModal.error = '';
            this.filterNameModal.resolve = null;
          },
          confirmFilterNameModal() {
            const name = typeof this.filterNameModal.name === 'string'
              ? this.filterNameModal.name.trim()
              : '';

            if (!name) {
              this.filterNameModal.error = 'Вкажіть назву фільтру.';

              this.$nextTick(() => {
                const input = this.$refs?.filterNameInput;

                if (input && typeof input.focus === 'function') {
                  input.focus();

                  if (typeof input.select === 'function') {
                    input.select();
                  }
                }
              });

              return;
            }

            const resolver = this.filterNameModal.resolve;
            this.closeFilterNameModal();

            if (typeof resolver === 'function') {
              resolver(name);
            }
          },
          cancelFilterNameModal() {
            const resolver = this.filterNameModal.resolve;
            this.closeFilterNameModal();

            if (typeof resolver === 'function') {
              resolver(null);
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
          setContentManagementMenuFeedback(message, type = 'success') {
            if (this.contentManagement.menuFeedbackTimeout) {
              clearTimeout(this.contentManagement.menuFeedbackTimeout);
              this.contentManagement.menuFeedbackTimeout = null;
            }

            const normalizedMessage = typeof message === 'string' ? message.trim() : '';

            if (!normalizedMessage) {
              this.contentManagement.menuFeedback = '';
              this.contentManagement.menuFeedbackType = 'success';
              return;
            }

            this.contentManagement.menuFeedback = normalizedMessage;
            this.contentManagement.menuFeedbackType = type === 'error' ? 'error' : 'success';

            this.contentManagement.menuFeedbackTimeout = window.setTimeout(() => {
              this.contentManagement.menuFeedback = '';
              this.contentManagement.menuFeedbackTimeout = null;
            }, 4000);
          },
          async persistContentManagementMenu(updatedMenu) {
            if (!Array.isArray(updatedMenu)) {
              throw new Error('Не вдалося визначити меню для збереження.');
            }

            if (!this.contentManagementRoutes.menuUpdate) {
              throw new Error('Маршрут оновлення меню не налаштовано.');
            }

            const payload = updatedMenu
              .map((item) => normalizeContentManagementMenuItem(item))
              .filter(Boolean);

            const response = await fetch(this.contentManagementRoutes.menuUpdate, {
              method: 'PUT',
              headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken || '',
              },
              body: JSON.stringify({
                menu: payload,
              }),
            });

            if (!response.ok) {
              const payloadResponse = await response.json().catch(() => null);
              const message = payloadResponse?.message || 'Не вдалося оновити меню Content Management.';
              throw new Error(message);
            }

            const responsePayload = await response.json().catch(() => null);
            const normalizedMenu = normalizeContentManagementMenu(responsePayload?.menu ?? responsePayload);

            if (!Array.isArray(normalizedMenu) || normalizedMenu.length !== payload.length) {
              this.contentManagement.menu = Array.isArray(normalizedMenu) ? normalizedMenu : this.contentManagement.menu;
              throw new Error('Отримано некоректну відповідь під час оновлення меню.');
            }

            this.contentManagement.menu = normalizedMenu;
            const defaultItem = normalizedMenu.find((item) => item && item.is_default);
            this.contentManagement.defaultTable = defaultItem && defaultItem.table ? defaultItem.table : '';
            this.contentManagement.menuSettings.error = null;

            return normalizedMenu;
          },
          async setContentManagementDefaultTable(tableName) {
            const normalized = typeof tableName === 'string' ? tableName.trim() : '';

            if (!normalized || this.contentManagement.menuSettings.saving) {
              return;
            }

            if (this.contentManagement.defaultTable === normalized) {
              return;
            }

            const currentMenu = Array.isArray(this.contentManagement.menu)
              ? this.contentManagement.menu
              : [];
            const hasTarget = currentMenu.some((item) => item && item.table === normalized);

            if (!hasTarget) {
              return;
            }

            const previousMenu = currentMenu.map((item) => ({
              table: item.table,
              label: item.label,
              is_default: item.is_default,
            }));
            const updatedMenu = currentMenu.map((item) => {
              if (!item) {
                return item;
              }

              return {
                ...item,
                is_default: item.table === normalized,
              };
            });

            this.contentManagement.menu = updatedMenu;
            this.contentManagement.defaultTable = normalized;
            this.contentManagement.menuSettings.saving = true;
            this.contentManagement.menuSettings.error = null;
            this.setContentManagementMenuFeedback('', 'success');

            try {
              await this.persistContentManagementMenu(updatedMenu);
              this.setContentManagementMenuFeedback('Таблицю встановлено за замовчуванням.', 'success');

              if (this.activeTab === 'content-management') {
                await this.selectContentManagementTable(normalized);
              }
            } catch (error) {
              this.contentManagement.menu = previousMenu;
              const previousDefault = previousMenu.find((item) => item && item.is_default);
              this.contentManagement.defaultTable = previousDefault && previousDefault.table
                ? previousDefault.table
                : '';
              this.contentManagement.menuSettings.error = error?.message ?? 'Сталася помилка під час оновлення меню.';
              this.setContentManagementMenuFeedback('', 'success');
            } finally {
              this.contentManagement.menuSettings.saving = false;
            }
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

              const updatedMenu = normalizeContentManagementMenu(
                this.contentManagement.menu
                  .filter((item) => item && item.table !== normalizedItem.table)
                  .concat([normalizedItem]),
              );

              this.contentManagement.menu = updatedMenu;
              const defaultItem = updatedMenu.find((item) => item && item.is_default);
              this.contentManagement.defaultTable = defaultItem && defaultItem.table ? defaultItem.table : '';
              this.closeContentManagementMenuSettings();
              await this.selectContentManagementTable(normalizedItem.table);
              this.setContentManagementMenuFeedback('Таблицю додано до меню.', 'success');
            } catch (error) {
              this.contentManagement.menuSettings.error = error?.message ?? 'Сталася помилка під час збереження меню.';
            } finally {
              this.contentManagement.menuSettings.saving = false;
            }
          },
          async renameContentManagementMenuItem(tableName) {
            const normalized = typeof tableName === 'string' ? tableName.trim() : '';

            if (!normalized || this.contentManagement.menuSettings.saving) {
              return;
            }

            const currentItem = this.contentManagement.menu.find((entry) => entry && entry.table === normalized);

            if (!currentItem) {
              return;
            }

            const currentLabel = currentItem.label || currentItem.table;
            const newLabel = window.prompt('Нова назва таблиці в меню', currentLabel);

            if (newLabel === null) {
              return;
            }

            const trimmed = typeof newLabel === 'string' ? newLabel.trim() : '';
            const previousMenu = this.contentManagement.menu.slice();
            const updatedMenu = this.contentManagement.menu.map((entry) => {
              if (!entry || entry.table !== normalized) {
                return entry;
              }

              return {
                table: entry.table,
                label: trimmed || entry.table,
                is_default: entry.is_default === true,
              };
            });

            this.contentManagement.menu = updatedMenu;
            this.contentManagement.menuSettings.saving = true;
            this.contentManagement.menuSettings.error = null;
            this.setContentManagementMenuFeedback('', 'success');

            try {
              await this.persistContentManagementMenu(updatedMenu);
              this.setContentManagementMenuFeedback('Назву таблиці оновлено.', 'success');
            } catch (error) {
              this.contentManagement.menu = previousMenu;
              this.contentManagement.menuSettings.error = error?.message ?? 'Сталася помилка під час оновлення меню.';
              this.setContentManagementMenuFeedback('', 'success');
            } finally {
              this.contentManagement.menuSettings.saving = false;
            }
          },
          async moveContentManagementMenuItem(tableName, direction) {
            const normalized = typeof tableName === 'string' ? tableName.trim() : '';
            const offset = Number(direction);

            if (!normalized || !Number.isFinite(offset) || offset === 0 || this.contentManagement.menuSettings.saving) {
              return;
            }

            const index = this.contentManagement.menu.findIndex((entry) => entry && entry.table === normalized);

            if (index === -1) {
              return;
            }

            const step = offset > 0 ? 1 : -1;
            const targetIndex = index + step;

            if (targetIndex < 0 || targetIndex >= this.contentManagement.menu.length) {
              return;
            }

            const previousMenu = this.contentManagement.menu.slice();
            const updatedMenu = previousMenu.slice();
            const [moved] = updatedMenu.splice(index, 1);

            if (!moved) {
              return;
            }

            updatedMenu.splice(targetIndex, 0, moved);

            this.contentManagement.menu = updatedMenu;
            this.contentManagement.menuSettings.saving = true;
            this.contentManagement.menuSettings.error = null;
            this.setContentManagementMenuFeedback('', 'success');

            try {
              await this.persistContentManagementMenu(updatedMenu);
              this.setContentManagementMenuFeedback('Порядок меню оновлено.', 'success');
            } catch (error) {
              this.contentManagement.menu = previousMenu;
              this.contentManagement.menuSettings.error = error?.message ?? 'Сталася помилка під час оновлення меню.';
              this.setContentManagementMenuFeedback('', 'success');
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
              this.setContentManagementMenuFeedback('Таблицю видалено з меню.', 'success');

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
          contentManagementTableUrl(tableName) {
            const normalized = typeof tableName === 'string' ? tableName.trim() : '';
            const base = this.tabRoutes?.['content-management'];

            if (!normalized || !base) {
              return '';
            }

            try {
              const url = new URL(base, window.location.origin);
              url.searchParams.set('table', normalized);
              return url.toString();
            } catch (error) {
              return '';
            }
          },
          async copyContentManagementTableUrl(tableName) {
            if (this.contentManagement.copyingUrl) {
              return;
            }

            const url = this.contentManagementTableUrl(tableName);

            if (!url) {
              this.setContentManagementMenuFeedback('Не вдалося сформувати посилання для цієї таблиці.', 'error');
              return;
            }

            this.contentManagement.copyingUrl = true;

            try {
              if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
                await navigator.clipboard.writeText(url);
              } else {
                const textarea = document.createElement('textarea');
                textarea.value = url;
                textarea.setAttribute('readonly', '');
                textarea.style.position = 'absolute';
                textarea.style.left = '-9999px';
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
              }

              this.setContentManagementMenuFeedback('Посилання на таблицю скопійовано.', 'success');
            } catch (error) {
              this.setContentManagementMenuFeedback('Не вдалося скопіювати посилання. Спробуйте ще раз.', 'error');
            } finally {
              this.contentManagement.copyingUrl = false;
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
          getContentManagementColumnsForSettings(tableName) {
            const normalized = typeof tableName === 'string' ? tableName.trim() : '';

            if (!normalized) {
              return [];
            }

            const viewerColumns =
              this.contentManagement.viewer &&
              this.contentManagement.viewer.table === normalized &&
              Array.isArray(this.contentManagement.viewer.columns)
                ? this.contentManagement.viewer.columns
                  .map((column) => (typeof column === 'string' ? column.trim() : ''))
                  .filter((column) => column !== '')
                : [];

            const table = this.findTableByName(normalized);
            const structureColumns = table ? this.getTableColumnNames(table) : [];

            const hiddenColumns = this.getContentManagementHiddenColumns(normalized);
            const orderColumns = this.getContentManagementColumnOrder(normalized);
            const unique = new Set([
              ...structureColumns,
              ...viewerColumns,
              ...(Array.isArray(hiddenColumns) ? hiddenColumns : []),
              ...(Array.isArray(orderColumns) ? orderColumns : []),
            ]);

            const columns = Array.from(unique)
              .map((column) => (typeof column === 'string' ? column.trim() : ''))
              .filter((column) => column !== '');

            return this.orderContentManagementColumns(normalized, columns);
          },
          resolveContentManagementRelationState(tableName, columnName, override = null, preferred = '') {
            const normalizedTable = typeof tableName === 'string' ? tableName.trim() : '';
            const normalizedColumn = typeof columnName === 'string' ? columnName.trim() : '';

            if (!normalizedTable || !normalizedColumn) {
              return null;
            }

            const overrideSource = override && typeof override.source === 'string'
              ? override.source.trim()
              : (override && typeof override.source_column === 'string'
                ? override.source_column.trim()
                : (override && typeof override.sourceColumn === 'string'
                  ? override.sourceColumn.trim()
                  : ''));
            let lookupColumn = normalizedColumn;

            if (overrideSource) {
              lookupColumn = overrideSource;
            } else if (normalizedColumn.includes('::')) {
              lookupColumn = normalizedColumn.split('::', 1)[0]?.trim() || normalizedColumn;
            }

            const column = this.findTableColumn(normalizedTable, lookupColumn);

            if (!column || !column.foreign) {
              return null;
            }

            const foreign = column.foreign;
            const foreignTable = typeof foreign.table === 'string' ? foreign.table.trim() : '';
            const foreignColumn = typeof foreign.column === 'string' ? foreign.column.trim() : '';

            if (!foreignTable || !foreignColumn) {
              return null;
            }

            const overrideTable = override && typeof override.table === 'string' ? override.table.trim() : '';
            const overrideDisplay = override && typeof override.column === 'string' ? override.column.trim() : '';
            const preferredDisplay = typeof preferred === 'string' ? preferred.trim() : '';
            const foreignDisplay = typeof foreign.displayColumn === 'string' ? foreign.displayColumn.trim() : '';

            const relationTable = overrideTable || foreignTable;

            if (!relationTable) {
              return null;
            }

            const targetTable = this.findTableByName(relationTable);
            const targetColumns = this.getTableColumnNames(targetTable);
            const optionSet = new Set(Array.isArray(targetColumns) ? targetColumns : []);

            [overrideDisplay, foreignDisplay, preferredDisplay, foreignColumn].forEach((value) => {
              if (typeof value === 'string' && value !== '') {
                optionSet.add(value);
              }
            });

            const options = Array.from(optionSet);
            const selectedDisplay = preferredDisplay || overrideDisplay || foreignDisplay || '';

            const sourceColumn = lookupColumn;
            const isAdditional = normalizedColumn !== sourceColumn;

            return {
              key: normalizedColumn,
              sourceColumn,
              table: relationTable,
              originalTable: foreignTable,
              referencedColumn: foreignColumn,
              displayColumn: selectedDisplay,
              options,
              manual: Boolean(foreign.manual),
              additional: isAdditional,
            };
          },
          async updateContentManagementEntryRelation(entry) {
            if (!entry || typeof entry !== 'object') {
              return;
            }

            const tableName = typeof this.contentManagement.tableSettings.table === 'string'
              ? this.contentManagement.tableSettings.table.trim()
              : '';

            if (!tableName) {
              entry.relation = null;
              return;
            }

            const rawColumnName = typeof entry.column === 'string' ? entry.column.trim() : '';
            const existingRelation = entry.relation && typeof entry.relation === 'object'
              ? entry.relation
              : null;
            const relationKey = rawColumnName
              || (existingRelation && typeof existingRelation.key === 'string'
                ? existingRelation.key.trim()
                : rawColumnName);

            const table = await this.ensureStructureLoadedByName(tableName);

            if (!table) {
              entry.relation = null;
              return;
            }

            const relations = this.getContentManagementRelationOverrides(tableName);
            const override = relations && typeof relations === 'object' ? relations[relationKey] : null;
            const preferred = entry.relation
              && typeof entry.relation.displayColumn === 'string'
              ? entry.relation.displayColumn
              : '';

            const lookupColumn = existingRelation && typeof existingRelation.sourceColumn === 'string'
              ? existingRelation.sourceColumn.trim()
              : (relationKey && relationKey.includes('::')
                ? relationKey.split('::', 1)[0]
                : relationKey);

            if (!lookupColumn) {
              entry.relation = null;
              return;
            }

            const column = this.findTableColumn(table, lookupColumn);
            const foreignTable = column
              && column.foreign
              && typeof column.foreign.table === 'string'
              ? column.foreign.table.trim()
              : '';
            const overrideTable = override && typeof override.table === 'string' ? override.table.trim() : '';

            if (foreignTable) {
              await this.ensureStructureLoadedByName(foreignTable);
            }

            if (overrideTable && overrideTable !== foreignTable) {
              await this.ensureStructureLoadedByName(overrideTable);
            }

            const relationState = this.resolveContentManagementRelationState(
              tableName,
              relationKey || lookupColumn,
              override,
              preferred,
            );

            entry.relation = relationState ?? null;

            if (relationState) {
              entry.column = this.contentManagementEntryColumnKey(entry);
            }

            if (relationState && relationState.displayColumn) {
              entry.hidden = false;
            }
          },
          nextContentManagementTableSettingsId() {
            const current = Number(this.contentManagement.tableSettings.nextId) || 0;
            this.contentManagement.tableSettings.nextId = current + 1;
            return `cm-table-settings-${Date.now()}-${current}`;
          },
          async openContentManagementTableSettingsModal() {
            const tableName = typeof this.contentManagement.selectedTable === 'string'
              ? this.contentManagement.selectedTable.trim()
              : '';

            if (!tableName) {
              return;
            }

            this.contentManagement.tableSettings.nextId = 0;

            const table = await this.ensureStructureLoadedByName(tableName);
            const aliases = this.getContentManagementTableAliases(tableName);
            const hiddenColumns = this.getContentManagementHiddenColumns(tableName);
            const relationsSource = this.getContentManagementRelationOverrides(tableName);
            const relationOverrides = relationsSource && typeof relationsSource === 'object'
              ? { ...relationsSource }
              : {};
            const relationOverridesForOptions = relationsSource && typeof relationsSource === 'object'
              ? { ...relationsSource }
              : {};
            const relationTargets = new Set();
            const hiddenSet = new Set(
              Array.isArray(hiddenColumns)
                ? hiddenColumns
                  .map((column) => (typeof column === 'string' ? column.trim() : ''))
                  .filter((column) => column !== '')
                : [],
            );
            const structureColumns = table && table.structure && Array.isArray(table.structure.columns)
              ? table.structure.columns
              : [];

            structureColumns.forEach((columnDefinition) => {
              const relatedTable = columnDefinition
                && columnDefinition.foreign
                && typeof columnDefinition.foreign.table === 'string'
                ? columnDefinition.foreign.table.trim()
                : '';

              if (relatedTable) {
                relationTargets.add(relatedTable);
              }
            });

            Object.values(relationOverrides).forEach((definition) => {
              const relatedTable = definition && typeof definition.table === 'string'
                ? definition.table.trim()
                : '';

              if (relatedTable) {
                relationTargets.add(relatedTable);
              }
            });

            for (const relatedTable of relationTargets) {
              await this.ensureStructureLoadedByName(relatedTable);
            }

            const columns = this.getContentManagementColumnsForSettings(tableName);
            const seen = new Set();
            const entries = [];

            columns.forEach((column) => {
              const normalizedColumn = typeof column === 'string' ? column.trim() : '';

              if (!normalizedColumn) {
                return;
              }

              const aliasValue = typeof aliases[normalizedColumn] === 'string' ? aliases[normalizedColumn] : '';
              const relation = this.resolveContentManagementRelationState(
                tableName,
                normalizedColumn,
                relationOverrides[normalizedColumn] ?? null,
              );

              entries.push({
                id: this.nextContentManagementTableSettingsId(),
                column: normalizedColumn,
                alias: aliasValue,
                hidden: hiddenSet.has(normalizedColumn),
                locked: true,
                relation: relation ?? null,
              });

              seen.add(normalizedColumn);
              hiddenSet.delete(normalizedColumn);

              if (Object.prototype.hasOwnProperty.call(relationOverrides, normalizedColumn)) {
                delete relationOverrides[normalizedColumn];
              }
            });

            Object.entries(aliases).forEach(([column, alias]) => {
              const normalizedColumn = typeof column === 'string' ? column.trim() : '';

              if (!normalizedColumn || seen.has(normalizedColumn)) {
                return;
              }

              const relation = this.resolveContentManagementRelationState(
                tableName,
                normalizedColumn,
                relationOverrides[normalizedColumn] ?? null,
              );

              entries.push({
                id: this.nextContentManagementTableSettingsId(),
                column: normalizedColumn,
                alias: typeof alias === 'string' ? alias : '',
                hidden: hiddenSet.has(normalizedColumn),
                locked: relation && relation.additional ? true : false,
                relation: relation ?? null,
              });

              seen.add(normalizedColumn);
              hiddenSet.delete(normalizedColumn);

              if (Object.prototype.hasOwnProperty.call(relationOverrides, normalizedColumn)) {
                delete relationOverrides[normalizedColumn];
              }
            });

            hiddenSet.forEach((column) => {
              const normalizedColumn = typeof column === 'string' ? column.trim() : '';
              const relation = this.resolveContentManagementRelationState(
                tableName,
                normalizedColumn,
                relationOverrides[normalizedColumn] ?? null,
              );

              entries.push({
                id: this.nextContentManagementTableSettingsId(),
                column,
                alias: '',
                hidden: true,
                locked: relation && relation.additional ? true : false,
                relation: relation ?? null,
              });
              seen.add(column);

              if (Object.prototype.hasOwnProperty.call(relationOverrides, normalizedColumn)) {
                delete relationOverrides[normalizedColumn];
              }
            });

            Object.entries(relationOverrides).forEach(([column, definition]) => {
              const normalizedColumn = typeof column === 'string' ? column.trim() : '';

              if (!normalizedColumn || seen.has(normalizedColumn)) {
                return;
              }

              const relation = this.resolveContentManagementRelationState(
                tableName,
                normalizedColumn,
                definition,
              );

              entries.push({
                id: this.nextContentManagementTableSettingsId(),
                column: normalizedColumn,
                alias: '',
                hidden: false,
                locked: relation && relation.additional ? true : false,
                relation: relation ?? null,
              });

              seen.add(normalizedColumn);
            });

            if (entries.length > 1) {
              const orderedKeys = this.getContentManagementColumnOrder(tableName);

              if (Array.isArray(orderedKeys) && orderedKeys.length > 0) {
                const positionMap = new Map();

                orderedKeys.forEach((key, index) => {
                  if (typeof key !== 'string') {
                    return;
                  }

                  const normalizedKey = key.trim();

                  if (normalizedKey && !positionMap.has(normalizedKey)) {
                    positionMap.set(normalizedKey, index);
                  }
                });

                if (positionMap.size > 0) {
                  entries.sort((a, b) => {
                    const keyA = this.contentManagementEntryColumnKey(a);
                    const keyB = this.contentManagementEntryColumnKey(b);
                    const normalizedA = typeof keyA === 'string' ? keyA.trim() : '';
                    const normalizedB = typeof keyB === 'string' ? keyB.trim() : '';
                    const positionA = positionMap.has(normalizedA)
                      ? positionMap.get(normalizedA)
                      : Number.POSITIVE_INFINITY;
                    const positionB = positionMap.has(normalizedB)
                      ? positionMap.get(normalizedB)
                      : Number.POSITIVE_INFINITY;

                    if (positionA !== positionB) {
                      return positionA - positionB;
                    }

                    if (normalizedA && normalizedB) {
                      return normalizedA.localeCompare(normalizedB, 'uk');
                    }

                    if (normalizedA) {
                      return -1;
                    }

                    if (normalizedB) {
                      return 1;
                    }

                    return 0;
                  });
                }
              }
            }

            const relationOptions = this.buildContentManagementRelationOptions(
              tableName,
              relationOverridesForOptions,
            );

            if (entries.length === 0) {
              entries.push({
                id: this.nextContentManagementTableSettingsId(),
                column: '',
                alias: '',
                hidden: false,
                locked: false,
                relation: null,
              });
            }

            this.contentManagement.tableSettings.table = tableName;
            this.contentManagement.tableSettings.entries = entries;
            this.contentManagement.tableSettings.error = null;
            this.contentManagement.tableSettings.feedback = '';
            this.contentManagement.tableSettings.relationOptions = relationOptions;
            this.contentManagement.tableSettings.selectedRelationOption = '';
            this.contentManagement.tableSettings.open = true;
          },
          closeContentManagementTableSettings() {
            this.resetContentManagementTableSettings();
          },
          resetContentManagementTableSettings() {
            this.contentManagement.tableSettings.open = false;
            this.contentManagement.tableSettings.table = '';
            this.contentManagement.tableSettings.entries = [];
            this.contentManagement.tableSettings.error = null;
            this.contentManagement.tableSettings.feedback = '';
            this.contentManagement.tableSettings.nextId = 0;
            this.contentManagement.tableSettings.relationOptions = [];
            this.contentManagement.tableSettings.selectedRelationOption = '';
          },
          addContentManagementTableSettingsEntry() {
            if (!Array.isArray(this.contentManagement.tableSettings.entries)) {
              this.contentManagement.tableSettings.entries = [];
            }

            this.contentManagement.tableSettings.entries = [
              ...this.contentManagement.tableSettings.entries,
              {
                id: this.nextContentManagementTableSettingsId(),
                column: '',
                alias: '',
                hidden: false,
                locked: false,
                relation: null,
              },
            ];

            this.contentManagement.tableSettings.feedback = '';
            this.contentManagement.tableSettings.error = null;
          },
          removeContentManagementTableSettingsEntry(index) {
            const entries = Array.isArray(this.contentManagement.tableSettings.entries)
              ? this.contentManagement.tableSettings.entries
              : [];

            this.contentManagement.tableSettings.entries = entries.filter(
              (_entry, entryIndex) => entryIndex !== index,
            );

            this.contentManagement.tableSettings.feedback = '';
            this.contentManagement.tableSettings.error = null;
          },
          moveContentManagementTableSettingsEntry(fromIndex, toIndex) {
            const entries = Array.isArray(this.contentManagement.tableSettings.entries)
              ? [...this.contentManagement.tableSettings.entries]
              : [];

            if (
              !Number.isInteger(fromIndex) ||
              !Number.isInteger(toIndex) ||
              fromIndex < 0 ||
              toIndex < 0 ||
              fromIndex >= entries.length ||
              toIndex >= entries.length ||
              fromIndex === toIndex
            ) {
              return;
            }

            const [moved] = entries.splice(fromIndex, 1);
            entries.splice(toIndex, 0, moved);

            this.contentManagement.tableSettings.entries = entries;
            this.contentManagement.tableSettings.feedback = '';
            this.contentManagement.tableSettings.error = null;
          },
          contentManagementRelationConfigKey(sourceColumn, relationTable, displayColumn) {
            const base = typeof sourceColumn === 'string' ? sourceColumn.trim() : '';
            const table = typeof relationTable === 'string' ? relationTable.trim() : '';
            const display = typeof displayColumn === 'string' ? displayColumn.trim() : '';

            if (!base || !display) {
              return base || display || '';
            }

            return `${base}::${table}::${display}`;
          },
          contentManagementEntryColumnKey(entry) {
            if (!entry || typeof entry !== 'object') {
              return '';
            }

            const baseColumn = typeof entry.column === 'string' ? entry.column.trim() : '';
            const relation = entry.relation && typeof entry.relation === 'object' ? entry.relation : null;

            if (!relation) {
              return baseColumn;
            }

            const displayColumn = typeof relation.displayColumn === 'string'
              ? relation.displayColumn.trim()
              : '';

            if (!displayColumn) {
              return baseColumn;
            }

            const sourceColumn = typeof relation.sourceColumn === 'string'
              ? relation.sourceColumn.trim()
              : (baseColumn.includes('::') ? baseColumn.split('::', 1)[0] : baseColumn);
            const relationTable = typeof relation.table === 'string' ? relation.table.trim() : '';

            if (relation.additional || (baseColumn && baseColumn.includes('::'))) {
              return this.contentManagementRelationConfigKey(
                sourceColumn,
                relationTable,
                displayColumn,
              );
            }

            return sourceColumn || baseColumn;
          },
          collectContentManagementTableSettingsAliases() {
            const entries = Array.isArray(this.contentManagement.tableSettings.entries)
              ? this.contentManagement.tableSettings.entries
              : [];

            return entries.reduce((carry, entry) => {
              const column = this.contentManagementEntryColumnKey(entry);
              const alias = typeof entry?.alias === 'string' ? entry.alias.trim() : '';

              if (column && alias) {
                carry[column] = alias;
              }

              return carry;
            }, {});
          },
          collectContentManagementTableSettingsHiddenColumns() {
            const entries = Array.isArray(this.contentManagement.tableSettings.entries)
              ? this.contentManagement.tableSettings.entries
              : [];

            const hidden = new Set();

            entries.forEach((entry) => {
              const column = this.contentManagementEntryColumnKey(entry);

              if (!column) {
                return;
              }

              const relation = entry?.relation && typeof entry.relation === 'object'
                ? entry.relation
                : null;
              const relationDisplay = typeof relation?.displayColumn === 'string'
                ? relation.displayColumn.trim()
                : '';

              if (relation && relationDisplay) {
                return;
              }

              const value = entry?.hidden;
              const isHidden = value === true || value === 'true' || value === '1' || value === 1;

              if (isHidden) {
                hidden.add(column);
              }
            });

            return Array.from(hidden);
          },
          collectContentManagementTableSettingsRelations() {
            const entries = Array.isArray(this.contentManagement.tableSettings.entries)
              ? this.contentManagement.tableSettings.entries
              : [];

            return entries.reduce((carry, entry) => {
              const column = this.contentManagementEntryColumnKey(entry);

              if (!column) {
                return carry;
              }

              const relation = entry?.relation && typeof entry.relation === 'object'
                ? entry.relation
                : null;

              if (!relation) {
                return carry;
              }

              const displayColumn = typeof relation.displayColumn === 'string'
                ? relation.displayColumn.trim()
                : '';

              if (!displayColumn) {
                return carry;
              }

              const relationTable = typeof relation.table === 'string' ? relation.table.trim() : '';
              const sourceColumn = typeof relation.sourceColumn === 'string'
                ? relation.sourceColumn.trim()
                : '';

              carry[column] = {
                column: displayColumn,
                ...(relationTable ? { table: relationTable } : {}),
                ...((relation.additional || (sourceColumn && sourceColumn !== column))
                  ? { source: sourceColumn || column }
                  : {}),
              };

              return carry;
            }, {});
          },
          collectContentManagementTableSettingsOrder() {
            const entries = Array.isArray(this.contentManagement.tableSettings.entries)
              ? this.contentManagement.tableSettings.entries
              : [];

            const order = [];
            const seen = new Set();

            entries.forEach((entry) => {
              const key = this.contentManagementEntryColumnKey(entry);
              const normalizedKey = typeof key === 'string' ? key.trim() : '';

              if (!normalizedKey || seen.has(normalizedKey)) {
                return;
              }

              seen.add(normalizedKey);
              order.push(normalizedKey);
            });

            return order;
          },
          buildContentManagementRelationOptions(tableName, relationOverrides = {}) {
            const normalizedTable = typeof tableName === 'string' ? tableName.trim() : '';

            if (!normalizedTable) {
              return [];
            }

            const table = this.findTableByName(normalizedTable);

            if (!table || !table.structure || !Array.isArray(table.structure.columns)) {
              return [];
            }

            const overrideMap = relationOverrides && typeof relationOverrides === 'object'
              ? { ...relationOverrides }
              : {};

            const groups = [];
            const groupMap = new Map();

            table.structure.columns.forEach((column) => {
              if (!column || typeof column.name !== 'string') {
                return;
              }

              const sourceColumn = column.name.trim();

              if (!sourceColumn) {
                return;
              }

              const foreign = column.foreign && typeof column.foreign === 'object'
                ? column.foreign
                : null;

              if (!foreign) {
                return;
              }

              const foreignTable = typeof foreign.table === 'string' ? foreign.table.trim() : '';
              const referencedColumn = typeof foreign.column === 'string' ? foreign.column.trim() : '';

              if (!foreignTable || !referencedColumn) {
                return;
              }

              const overrideDefinition = overrideMap[sourceColumn] ?? null;
              const overrideTable = overrideDefinition && typeof overrideDefinition.table === 'string'
                ? overrideDefinition.table.trim()
                : '';

              const relationTable = overrideTable || foreignTable;
              const targetTable = this.findTableByName(relationTable);
              const targetColumns = this.getTableColumnNames(targetTable);
              const optionSet = new Set(Array.isArray(targetColumns) ? targetColumns : []);
              const foreignDisplay = typeof foreign.displayColumn === 'string'
                ? foreign.displayColumn.trim()
                : '';

              if (foreignDisplay) {
                optionSet.add(foreignDisplay);
              }

              if (overrideDefinition) {
                if (typeof overrideDefinition === 'string') {
                  const normalized = overrideDefinition.includes('.')
                    ? overrideDefinition.split('.', 2)[1]?.trim() ?? ''
                    : overrideDefinition.trim();

                  if (normalized) {
                    optionSet.add(normalized);
                  }
                } else if (typeof overrideDefinition === 'object' && overrideDefinition !== null) {
                  const overrideColumn = typeof overrideDefinition.column === 'string'
                    ? overrideDefinition.column.trim()
                    : '';

                  if (overrideColumn) {
                    optionSet.add(overrideColumn);
                  }
                }
              }

              const normalizedOptions = Array.from(optionSet)
                .map((value) => (typeof value === 'string' ? value.trim() : ''))
                .filter((value) => value !== '');

              if (normalizedOptions.length === 0) {
                return;
              }

              const relationLabel = this.contentManagementLabel(relationTable)
                || this.humanizeContentManagementName(relationTable)
                || relationTable;
              const columnLabel = this.humanizeContentManagementName(sourceColumn) || sourceColumn;
              const groupKey = `${sourceColumn}::${relationTable}`;

              if (!groupMap.has(groupKey)) {
                groupMap.set(groupKey, {
                  key: groupKey,
                  sourceColumn,
                  relationTable,
                  referencedColumn,
                  label: `${columnLabel} → ${relationLabel}`,
                  manual: Boolean(foreign.manual),
                  options: [],
                });
                groups.push(groupMap.get(groupKey));
              }

              const group = groupMap.get(groupKey);

              const sortedOptions = normalizedOptions.sort((a, b) => a.localeCompare(b));

              sortedOptions.forEach((optionColumn) => {
                const optionKey = `${groupKey}::${optionColumn}`;
                group.options.push({
                  key: optionKey,
                  sourceColumn,
                  relationTable,
                  referencedColumn,
                  displayColumn: optionColumn,
                  label: `${relationLabel} · ${optionColumn}`,
                  aliasSuggestion: `${relationLabel} — ${this.humanizeContentManagementName(optionColumn) || optionColumn}`,
                  manual: group.manual,
                });
              });
            });

            groups.forEach((group) => {
              group.options.sort((a, b) => a.label.localeCompare(b.label, 'uk'));
            });

            groups.sort((a, b) => a.label.localeCompare(b.label, 'uk'));

            return groups;
          },
          findContentManagementRelationOption(key) {
            const optionKey = typeof key === 'string' ? key.trim() : '';

            if (!optionKey) {
              return null;
            }

            const groups = Array.isArray(this.contentManagement.tableSettings.relationOptions)
              ? this.contentManagement.tableSettings.relationOptions
              : [];

            for (const group of groups) {
              if (!group || !Array.isArray(group.options)) {
                continue;
              }

              const option = group.options.find((entry) => entry && entry.key === optionKey);

              if (option) {
                return { group, option };
              }
            }

            return null;
          },
          addContentManagementRelationColumn() {
            const selection = this.findContentManagementRelationOption(
              this.contentManagement.tableSettings.selectedRelationOption,
            );

            if (!selection || !selection.option) {
              return;
            }

            const tableName = typeof this.contentManagement.tableSettings.table === 'string'
              ? this.contentManagement.tableSettings.table.trim()
              : '';

            if (!tableName) {
              return;
            }

            const { option } = selection;
            const override = {
              table: option.relationTable,
              column: option.displayColumn,
            };

            let relationState = this.resolveContentManagementRelationState(
              tableName,
              option.sourceColumn,
              override,
              option.displayColumn,
            );

            if (relationState) {
              const optionSet = new Set(
                Array.isArray(relationState.options) ? relationState.options : [],
              );
              optionSet.add(option.displayColumn);
              relationState = {
                ...relationState,
                table: option.relationTable || relationState.table,
                options: Array.from(optionSet),
                displayColumn: option.displayColumn,
              };
            } else {
              relationState = {
                sourceColumn: option.sourceColumn,
                table: option.relationTable,
                originalTable: option.relationTable,
                referencedColumn: option.referencedColumn,
                displayColumn: option.displayColumn,
                options: [option.displayColumn],
                manual: Boolean(option.manual),
              };
            }

            relationState = {
              ...relationState,
              sourceColumn: relationState.sourceColumn || option.sourceColumn,
              additional: true,
            };

            const key = this.contentManagementRelationConfigKey(
              relationState.sourceColumn,
              relationState.table,
              relationState.displayColumn,
            );

            relationState.key = key;

            const entries = Array.isArray(this.contentManagement.tableSettings.entries)
              ? [...this.contentManagement.tableSettings.entries]
              : [];
            const index = entries.findIndex((entry) => {
              return this.contentManagementEntryColumnKey(entry) === key;
            });

            if (index === -1) {
              const newEntry = {
                id: this.nextContentManagementTableSettingsId(),
                column: key,
                alias: option.aliasSuggestion || '',
                hidden: false,
                locked: true,
                relation: relationState,
              };

              entries.push(newEntry);
            } else {
              const existing = entries[index] || {};
              const currentAlias = typeof existing.alias === 'string' ? existing.alias.trim() : '';
              const nextAlias = currentAlias || option.aliasSuggestion || '';
              const nextRelation = {
                ...(existing.relation && typeof existing.relation === 'object' ? existing.relation : {}),
                ...relationState,
              };

              nextRelation.key = key;

              entries[index] = {
                ...existing,
                column: key,
                alias: nextAlias,
                hidden: false,
                locked: true,
                relation: nextRelation,
              };
            }

            this.contentManagement.tableSettings.entries = entries;
            this.contentManagement.tableSettings.selectedRelationOption = '';
            this.contentManagement.tableSettings.feedback = '';
            this.contentManagement.tableSettings.error = null;
          },
          humanizeContentManagementName(value) {
            if (typeof value !== 'string') {
              return '';
            }

            const trimmed = value.trim();

            if (!trimmed) {
              return '';
            }

            return trimmed
              .split(/[_\s]+/)
              .filter((segment) => segment.length > 0)
              .map((segment) => segment.charAt(0).toUpperCase() + segment.slice(1))
              .join(' ');
          },
          contentManagementTableSettingsSnippet() {
            const tableName = typeof this.contentManagement.tableSettings.table === 'string'
              ? this.contentManagement.tableSettings.table.trim()
              : '';

            if (!tableName) {
              return '';
            }

            const aliases = this.collectContentManagementTableSettingsAliases();
            const hidden = this.collectContentManagementTableSettingsHiddenColumns();
            const relations = this.collectContentManagementTableSettingsRelations();
            const hasAliases = Object.keys(aliases).length > 0;
            const hasHidden = hidden.length > 0;
            const hasRelations = Object.keys(relations).length > 0;
            const order = this.collectContentManagementTableSettingsOrder();
            const hasOrder = order.length > 0;

            if (!hasAliases && !hasHidden && !hasRelations && !hasOrder) {
              return '';
            }

            if (!hasHidden && !hasRelations && !hasOrder) {
              return JSON.stringify({ [tableName]: aliases }, null, 2);
            }

            const payload = {};

            if (hasAliases) {
              payload.aliases = aliases;
            }

            if (hasHidden) {
              payload.hidden = hidden;
            }

            if (hasRelations) {
              payload.relations = relations;
            }

            if (hasOrder) {
              payload.order = order;
            }

            return JSON.stringify({ [tableName]: payload }, null, 2);
          },
          async copyContentManagementTableSettingsSnippet() {
            const snippet = this.contentManagementTableSettingsSnippet();

            if (!snippet) {
              this.contentManagement.tableSettings.error = 'Немає даних для копіювання. Додайте хоча б один alias, приховану колонку, порядок колонок або поле пов\'язаної таблиці.';
              this.contentManagement.tableSettings.feedback = '';
              return;
            }

            if (typeof navigator !== 'undefined' && navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
              try {
                await navigator.clipboard.writeText(snippet);
                this.contentManagement.tableSettings.feedback = 'JSON скопійовано у буфер обміну.';
                this.contentManagement.tableSettings.error = null;
                setTimeout(() => {
                  if (this.contentManagement.tableSettings.feedback === 'JSON скопійовано у буфер обміну.') {
                    this.contentManagement.tableSettings.feedback = '';
                  }
                }, 2500);
              } catch (error) {
                this.contentManagement.tableSettings.error = 'Не вдалося скопіювати JSON. Спробуйте вручну.';
                this.contentManagement.tableSettings.feedback = '';
              }

              return;
            }

            if (typeof document !== 'undefined') {
              try {
                const textarea = document.createElement('textarea');
                textarea.value = snippet;
                textarea.setAttribute('readonly', '');
                textarea.style.position = 'absolute';
                textarea.style.left = '-9999px';
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                this.contentManagement.tableSettings.feedback = 'JSON скопійовано у буфер обміну.';
                this.contentManagement.tableSettings.error = null;
                setTimeout(() => {
                  if (this.contentManagement.tableSettings.feedback === 'JSON скопійовано у буфер обміну.') {
                    this.contentManagement.tableSettings.feedback = '';
                  }
                }, 2500);
                return;
              } catch (error) {
                this.contentManagement.tableSettings.error = 'Не вдалося скопіювати JSON. Скопіюйте текст вручну.';
                this.contentManagement.tableSettings.feedback = '';
                return;
              }
            }

            this.contentManagement.tableSettings.error = 'Неможливо скопіювати JSON у цьому середовищі.';
            this.contentManagement.tableSettings.feedback = '';
          },
          applyContentManagementTableSettings() {
            const tableName = typeof this.contentManagement.tableSettings.table === 'string'
              ? this.contentManagement.tableSettings.table.trim()
              : '';

            if (!tableName) {
              this.contentManagement.tableSettings.error = 'Не вдалося визначити таблицю для налаштування.';
              this.contentManagement.tableSettings.feedback = '';
              return;
            }

            const aliases = this.collectContentManagementTableSettingsAliases();
            const hidden = this.collectContentManagementTableSettingsHiddenColumns();
            const relations = this.collectContentManagementTableSettingsRelations();
            const hasAliases = Object.keys(aliases).length > 0;
            const hasHidden = hidden.length > 0;
            const hasRelations = Object.keys(relations).length > 0;
            const order = this.collectContentManagementTableSettingsOrder();
            const hasOrder = order.length > 0;
            const currentSettings =
              this.contentManagement.settings && typeof this.contentManagement.settings === 'object'
                ? { ...this.contentManagement.settings }
                : {};

            if (hasAliases || hasHidden || hasRelations || hasOrder) {
              if (!hasHidden && !hasRelations && hasAliases && !hasOrder) {
                currentSettings[tableName] = aliases;
              } else {
                const payload = {};

                if (hasAliases) {
                  payload.aliases = aliases;
                }

                if (hasHidden) {
                  payload.hidden = hidden;
                }

                if (hasRelations) {
                  payload.relations = relations;
                }

                if (hasOrder) {
                  payload.order = order;
                }

                currentSettings[tableName] = payload;
              }
            } else {
              delete currentSettings[tableName];
            }

            this.contentManagement.settings = currentSettings;
            this.contentManagement.tableSettings.error = null;
            this.contentManagement.tableSettings.feedback = '';
            this.closeContentManagementTableSettings();

            if (this.contentManagement.selectedTable === tableName) {
              this.refreshContentManagementTable();
            }
          },
          getContentManagementTableAliases(tableName) {
            const normalized = typeof tableName === 'string' ? tableName.trim() : '';

            if (!normalized) {
              return {};
            }

            const settings = this.contentManagement && this.contentManagement.settings
              ? this.contentManagement.settings
              : {};

            const tableConfig = settings && typeof settings === 'object' ? settings[normalized] : null;

            if (!tableConfig) {
              return {};
            }

            if (Array.isArray(tableConfig)) {
              return normalizeAliasMap(tableConfig);
            }

            if (tableConfig && typeof tableConfig === 'object') {
              if (tableConfig.aliases && typeof tableConfig.aliases === 'object') {
                return normalizeAliasMap(tableConfig.aliases);
              }

              return normalizeAliasMap(tableConfig);
            }

            return {};
          },
          getContentManagementHiddenColumns(tableName) {
            const normalized = typeof tableName === 'string' ? tableName.trim() : '';

            if (!normalized) {
              return [];
            }

            const settings = this.contentManagement && this.contentManagement.settings
              ? this.contentManagement.settings
              : {};

            const tableConfig = settings && typeof settings === 'object' ? settings[normalized] : null;

            if (!tableConfig) {
              return [];
            }

            if (Array.isArray(tableConfig)) {
              return normalizeHiddenColumnsList(tableConfig);
            }

            if (tableConfig && typeof tableConfig === 'object') {
              if (tableConfig.hidden !== undefined) {
                const normalizedHidden = normalizeHiddenColumnsList(tableConfig.hidden);

                if (normalizedHidden.length > 0) {
                  return normalizedHidden;
                }
              }

              const alternateKeys = ['hidden_columns', 'hiddenColumns', 'columns_hidden', 'columnsHidden', 'hide'];

              for (const key of alternateKeys) {
                if (tableConfig[key] !== undefined) {
                  const normalizedHidden = normalizeHiddenColumnsList(tableConfig[key]);

                  if (normalizedHidden.length > 0) {
                    return normalizedHidden;
                  }
                }
              }

              if (!tableConfig.aliases) {
                const orderKeys = [
                  'order',
                  'column_order',
                  'columnOrder',
                  'columns_order',
                  'columnsOrder',
                  'order_columns',
                  'orderColumns',
                ];

                const sanitizedConfig = Object.fromEntries(
                  Object.entries(tableConfig).filter(([key]) => !orderKeys.includes(key)),
                );

                const fallback = normalizeHiddenColumnsList(sanitizedConfig);

                if (fallback.length > 0) {
                  return fallback;
                }
              }
            }

            return [];
          },
          getContentManagementColumnOrder(tableName) {
            const normalized = typeof tableName === 'string' ? tableName.trim() : '';

            if (!normalized) {
              return [];
            }

            const settings = this.contentManagement && this.contentManagement.settings
              ? this.contentManagement.settings
              : {};

            const tableConfig = settings && typeof settings === 'object' ? settings[normalized] : null;

            if (!tableConfig) {
              return [];
            }

            if (Array.isArray(tableConfig)) {
              return normalizeColumnOrderList(tableConfig);
            }

            if (tableConfig && typeof tableConfig === 'object') {
              const candidates = [
                tableConfig.order,
                tableConfig.column_order,
                tableConfig.columnOrder,
                tableConfig.columns_order,
                tableConfig.columnsOrder,
                tableConfig.order_columns,
                tableConfig.orderColumns,
              ];

              for (const candidate of candidates) {
                const normalizedOrder = normalizeColumnOrderList(candidate);

                if (normalizedOrder.length > 0) {
                  return normalizedOrder;
                }
              }
            }

            return [];
          },
          getContentManagementRelationOverrides(tableName) {
            const normalized = typeof tableName === 'string' ? tableName.trim() : '';

            if (!normalized) {
              return {};
            }

            const settings = this.contentManagement && this.contentManagement.settings
              ? this.contentManagement.settings
              : {};

            const tableConfig = settings && typeof settings === 'object' ? settings[normalized] : null;

            if (!tableConfig || typeof tableConfig !== 'object' || Array.isArray(tableConfig)) {
              return {};
            }

            const candidates = [
              tableConfig.relations,
              tableConfig.relation_columns,
              tableConfig.relationColumns,
              tableConfig.foreign_relations,
              tableConfig.foreignRelations,
              tableConfig.display_relations,
              tableConfig.displayRelations,
            ];

            for (const candidate of candidates) {
              if (candidate === undefined) {
                continue;
              }

              const normalizedMap = normalizeRelationDisplayMap(candidate, true);

              if (Object.keys(normalizedMap).length > 0) {
                return normalizedMap;
              }
            }

            return {};
          },
          filterContentManagementColumns(tableName, columns) {
            const normalized = typeof tableName === 'string' ? tableName.trim() : '';
            const list = Array.isArray(columns) ? columns : [];

            const sanitized = list
              .map((column) => (typeof column === 'string' ? column.trim() : ''))
              .filter((column) => column !== '');

            if (!normalized) {
              return sanitized;
            }

            const hidden = this.getContentManagementHiddenColumns(normalized);
            const hiddenSet = new Set(
              Array.isArray(hidden)
                ? hidden.map((column) => (typeof column === 'string' ? column.trim() : '')).filter((column) => column !== '')
                : [],
            );

            if (hiddenSet.size === 0) {
              return sanitized;
            }

            return sanitized.filter((column) => !hiddenSet.has(column));
          },
          orderContentManagementColumns(tableName, columns) {
            const normalized = typeof tableName === 'string' ? tableName.trim() : '';
            const list = Array.isArray(columns)
              ? columns.map((column) => (typeof column === 'string' ? column.trim() : '')).filter((column) => column !== '')
              : [];

            if (!normalized || list.length === 0) {
              return list;
            }

            const order = this.getContentManagementColumnOrder(normalized);

            if (!Array.isArray(order) || order.length === 0) {
              return list;
            }

            const normalizedOrder = order
              .map((column) => (typeof column === 'string' ? column.trim() : ''))
              .filter((column) => column !== '');

            if (normalizedOrder.length === 0) {
              return list;
            }

            const seen = new Set();
            const available = new Set(list);
            const ordered = [];

            normalizedOrder.forEach((column) => {
              if (available.has(column) && !seen.has(column)) {
                seen.add(column);
                ordered.push(column);
              }
            });

            list.forEach((column) => {
              if (!seen.has(column)) {
                seen.add(column);
                ordered.push(column);
              }
            });

            return ordered;
          },
          ensureContentManagementVisibleColumns(tableName) {
            const normalized = typeof tableName === 'string' ? tableName.trim() : '';

            if (!normalized || this.contentManagement.viewer.table !== normalized) {
              return;
            }

            const viewer = this.contentManagement.viewer;
            const orderedColumns = this.orderContentManagementColumns(normalized, viewer.columns);
            const visibleColumns = this.filterContentManagementColumns(normalized, orderedColumns);

            viewer.columns = visibleColumns;

            if (viewer.sort && !visibleColumns.includes(viewer.sort)) {
              viewer.sort = '';
              viewer.direction = 'asc';
            }

            if (viewer.searchColumn && !visibleColumns.includes(viewer.searchColumn)) {
              viewer.searchColumn = '';
            }

            if (Array.isArray(viewer.filters) && viewer.filters.length > 0) {
              viewer.filters = viewer.filters.filter((filter) => {
                if (!filter || !filter.column) {
                  return true;
                }

                return visibleColumns.includes(filter.column);
              });
            }
          },
          getContentManagementColumnAlias(tableName, columnName) {
            const normalizedTable = typeof tableName === 'string' ? tableName.trim() : '';
            const normalizedColumn = typeof columnName === 'string' ? columnName.trim() : '';

            if (!normalizedTable || !normalizedColumn) {
              return '';
            }

            const aliases = this.getContentManagementTableAliases(normalizedTable);
            const aliasValue = aliases && typeof aliases === 'object' ? aliases[normalizedColumn] : null;

            if (typeof aliasValue === 'string') {
              const trimmed = aliasValue.trim();

              if (trimmed !== '') {
                return trimmed;
              }
            }

            return '';
          },
          contentManagementColumnHeading(columnName) {
            const normalizedColumn = typeof columnName === 'string' ? columnName.trim() : '';

            if (!normalizedColumn) {
              return '';
            }

            const alias = this.getContentManagementColumnAlias(
              this.contentManagement.selectedTable,
              normalizedColumn,
            );

            return alias || normalizedColumn;
          },
          contentManagementColumnOptionLabel(columnName) {
            const normalizedColumn = typeof columnName === 'string' ? columnName.trim() : '';

            if (!normalizedColumn) {
              return '';
            }

            const alias = this.getContentManagementColumnAlias(
              this.contentManagement.selectedTable,
              normalizedColumn,
            );

          if (alias && alias !== normalizedColumn) {
            return `${alias} (${normalizedColumn})`;
          }

          return normalizedColumn;
        },
        contentManagementFilterShareUrl(filter) {
          if (!filter || typeof filter.id !== 'string') {
            return '';
          }

          const base = this.tabRoutes?.['content-management'];
          const tableName = typeof this.contentManagement.selectedTable === 'string'
            ? this.contentManagement.selectedTable.trim()
            : '';
          const filterId = filter.id.trim();

          if (!base || !tableName || !filterId) {
            return '';
          }

          try {
            const url = new URL(base, window.location.origin);
            url.searchParams.set('table', tableName);
            url.searchParams.set('content_filter', filterId);
            return url.toString();
          } catch (error) {
            return '';
          }
        },
        resetContentManagementViewer() {
          this.clearFeedback(this.contentManagement.viewer);
          const fresh = createContentManagementViewerState();
          const perPage = Number(this.contentManagement.viewer?.perPage) || fresh.perPage;

          this.contentManagement.viewer.table = fresh.table;
          this.contentManagement.viewer.loading = fresh.loading;
            this.contentManagement.viewer.error = fresh.error;
            this.contentManagement.viewer.columns = fresh.columns;
            this.contentManagement.viewer.rows = fresh.rows;
            this.contentManagement.viewer.sort = fresh.sort;
            this.contentManagement.viewer.direction = fresh.direction;
            this.contentManagement.viewer.filters = fresh.filters;
            this.contentManagement.viewer.search = fresh.search;
            this.contentManagement.viewer.searchInput = fresh.searchInput;
            this.contentManagement.viewer.searchColumn = fresh.searchColumn;
            this.contentManagement.viewer.page = 1;
            this.contentManagement.viewer.perPage = perPage;
          this.contentManagement.viewer.total = fresh.total;
          this.contentManagement.viewer.lastPage = fresh.lastPage;
          this.contentManagement.viewer.requestId = fresh.requestId;
          this.contentManagement.viewer.loaded = fresh.loaded;
          this.contentManagement.viewer.feedback = fresh.feedback;
          this.contentManagement.viewer.feedbackTimeout = fresh.feedbackTimeout;
          this.contentManagement.viewer.restoredFromStorage = fresh.restoredFromStorage;
          this.contentManagement.viewer.savedFilters = fresh.savedFilters;
        },
        async selectContentManagementTable(tableName, options = {}) {
            const normalized = typeof tableName === 'string' ? tableName.trim() : '';
            const normalizedOptions = options && typeof options === 'object' ? options : {};
            const normalizedFilterId = typeof normalizedOptions.filterId === 'string'
              ? normalizedOptions.filterId.trim()
              : '';

            if (normalizedFilterId) {
              this.contentManagement.pendingFilterId = normalizedFilterId;
            }

            if (!normalized) {
              return;
            }

            if (
              this.contentManagement.selectedTable === normalized &&
              this.contentManagement.viewer.loaded &&
              !normalizedFilterId
            ) {
              return;
            }

            this.contentManagement.selectedTable = normalized;
            this.contentManagement.preventAutoSelect = false;
            this.resetContentManagementViewer();

            await this.restoreContentManagementFilters(normalized, false, normalizedOptions);

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
        toggleContentManagementFilters(state = null) {
          const viewer = this.contentManagement && this.contentManagement.viewer
            ? this.contentManagement.viewer
            : null;

          if (!viewer) {
            return;
          }

          const nextState = typeof state === 'boolean' ? state : !viewer.filtersOpen;
          viewer.filtersOpen = nextState;
        },
        ensureContentManagementFiltersOpen() {
          const viewer = this.contentManagement && this.contentManagement.viewer
            ? this.contentManagement.viewer
            : null;

          if (!viewer) {
            return;
          }

          if (hasActiveContentFilters(viewer)) {
            viewer.filtersOpen = true;
          }
        },
        toggleContentManagementSort(column) {
          if (!this.contentManagement.selectedTable || this.contentManagement.viewer.loading) {
            return;
          }

            const normalized = typeof column === 'string' ? column.trim() : '';

            if (!normalized) {
              return;
            }

            const viewer = this.contentManagement.viewer;

            if (viewer.sort === normalized) {
              viewer.direction = viewer.direction === 'asc' ? 'desc' : 'asc';
            } else {
              viewer.sort = normalized;
              viewer.direction = 'asc';
            }

            viewer.page = 1;
            this.loadContentManagementTable(this.contentManagement.selectedTable);
          },
        addContentManagementFilter() {
          if (!this.contentManagement.selectedTable || this.contentManagement.viewer.loading) {
            return;
          }

          const viewer = this.contentManagement.viewer;
          viewer.filtersOpen = true;
          const structureTable = this.findTableByName(this.contentManagement.selectedTable);
            const structureColumns = this.getTableColumnNames(structureTable);
            const visibleStructureColumns = this.filterContentManagementColumns(
              this.contentManagement.selectedTable,
              structureColumns,
            );
            const availableColumns = Array.isArray(viewer.columns) && viewer.columns.length > 0
              ? viewer.columns
              : visibleStructureColumns;

            const fallbackColumn = availableColumns.length > 0 ? availableColumns[0] : '';

            viewer.filters = [
              ...viewer.filters,
              {
                id: this.generateFilterId(),
                column: fallbackColumn || '',
                operator: '=',
                value: '',
              },
            ];
          },
          removeContentManagementFilter(index) {
            const viewer = this.contentManagement.viewer;

            if (viewer.loading) {
              return;
            }

            if (index < 0 || index >= viewer.filters.length) {
              return;
            }

            viewer.filters = viewer.filters.filter((_, filterIndex) => filterIndex !== index);

            if (this.contentManagement.selectedTable && viewer.loaded) {
              viewer.page = 1;
              this.loadContentManagementTable(this.contentManagement.selectedTable);
            }
          },
          applyContentManagementFilters() {
            if (!this.contentManagement.selectedTable || this.contentManagement.viewer.loading) {
              return;
            }

            this.contentManagement.viewer.page = 1;
            this.loadContentManagementTable(this.contentManagement.selectedTable);
          },
          resetContentManagementFilters() {
            const viewer = this.contentManagement.viewer;

            if (viewer.loading || viewer.filters.length === 0) {
              return;
            }

            this.clearFeedback(viewer);
            viewer.filters = [];
            viewer.page = 1;

            if (this.contentManagement.selectedTable) {
              this.loadContentManagementTable(this.contentManagement.selectedTable);
            }
          },
        resolveContentManagementSearchColumn(tableName, column) {
          const normalizedColumn = typeof column === 'string' ? column.trim() : '';

          if (!normalizedColumn) {
            return '';
          }

          const table = this.findTableByName(tableName);
          const structureColumns = this.getTableColumnNames(table);
          const viewerColumns = Array.isArray(this.contentManagement.viewer.columns)
            ? this.contentManagement.viewer.columns
            : [];

          const available = new Set(
            [...structureColumns, ...viewerColumns]
              .map((item) => (typeof item === 'string' ? item.trim() : ''))
              .filter((item) => item !== ''),
          );

          if (available.size === 0 || available.has(normalizedColumn)) {
            return normalizedColumn;
          }

          return '';
        },
        applyContentSavedFilter(savedFilter) {
          if (!savedFilter) {
            return;
          }

          const viewer = this.contentManagement.viewer;
          const filters = Array.isArray(savedFilter.filters) ? savedFilter.filters : [];
          viewer.filters = this.normalizeFilters(filters, viewer.filters);

          const search = typeof savedFilter.search === 'string' ? savedFilter.search : '';
          viewer.search = search;
          viewer.searchInput = search;

          const tableName = typeof this.contentManagement.selectedTable === 'string'
            ? this.contentManagement.selectedTable.trim()
            : '';
          const searchColumn = this.resolveContentManagementSearchColumn(tableName, savedFilter.searchColumn);
          viewer.searchColumn = searchColumn;

          viewer.page = 1;
          this.ensureContentManagementFiltersOpen();
        },
        async applyContentSavedFilterById(filterId, options = {}) {
          if (!filterId) {
            return;
          }

          const viewer = this.contentManagement.viewer;
          const state = viewer.savedFilters;
          const target = Array.isArray(state.items)
            ? state.items.find((item) => item && item.id === filterId)
            : null;

          if (!target) {
            return;
          }

          this.applyContentSavedFilter(target);

          const shouldReload = options.reload === true;
          const shouldMark = options.markAsUsed === true;
          const updateLastUsed = options.updateLastUsed !== false;
          const tableName = typeof this.contentManagement.selectedTable === 'string'
            ? this.contentManagement.selectedTable.trim()
            : '';

          if (!tableName) {
            return;
          }

          if (shouldMark) {
            try {
              const response = await this.sendFilterRequest('PATCH', 'use', 'content', tableName, {
                filterId,
              });
              const normalized = normalizeSavedFiltersResponse(response || {});
              if (normalized.lastUsed) {
                state.lastUsed = normalized.lastUsed;
              } else if (updateLastUsed) {
                state.lastUsed = filterId;
              }
            } catch (error) {
              if (updateLastUsed) {
                state.lastUsed = filterId;
              }
            }
          } else if (updateLastUsed) {
            state.lastUsed = filterId;
          }

          if (shouldReload) {
            await this.loadContentManagementTable(tableName);
          }
        },
        toggleContentManagementDefaultFilter(filterId) {
          const viewer = this.contentManagement.viewer;

          if (!viewer || !viewer.savedFilters) {
            return;
          }

          const current = typeof viewer.savedFilters.defaultId === 'string'
            ? viewer.savedFilters.defaultId
            : '';

          if (current === filterId) {
            this.setContentManagementDefaultFilter('');
          } else {
            this.setContentManagementDefaultFilter(filterId);
          }
        },
        async setContentManagementDefaultFilter(filterId, options = {}) {
          const tableName = typeof this.contentManagement.selectedTable === 'string'
            ? this.contentManagement.selectedTable.trim()
            : '';

          if (!tableName) {
            this.setFeedback(this.contentManagement.viewer, 'Оберіть таблицю, щоб оновити фільтр за замовчуванням.');
            return;
          }

          await this.updateSavedFilterDefault(
            'content',
            tableName,
            typeof filterId === 'string' ? filterId : '',
            this.contentManagement.viewer.savedFilters,
            this.contentManagement.viewer,
          );

          if (options && options.resetActive === true) {
            const viewer = this.contentManagement.viewer;

            if (viewer) {
              const savedFilters = viewer.savedFilters;

              if (savedFilters) {
                savedFilters.lastUsed = '';
              }

              viewer.filters = [];
              viewer.search = '';
              viewer.searchInput = '';
              viewer.searchColumn = '';
              viewer.page = 1;
              this.contentManagement.pendingFilterId = '';

              await this.loadContentManagementTable(tableName);
            }
          }
        },
        async saveContentManagementFilters() {
          const tableName = typeof this.contentManagement.selectedTable === 'string'
            ? this.contentManagement.selectedTable.trim()
            : '';

          if (!tableName) {
            this.setFeedback(this.contentManagement.viewer, 'Оберіть таблицю для збереження фільтру.');
            return;
          }

          const viewer = this.contentManagement.viewer;

          if (viewer.loading) {
            return;
          }

          if (!this.filterRoutes.store) {
            this.setFeedback(viewer, 'Збереження фільтрів недоступне.');
            return;
          }

          const filters = serializeFilters(viewer.filters);
          const search = typeof viewer.search === 'string' ? viewer.search.trim() : '';
          const searchColumn = this.resolveContentManagementSearchColumn(tableName, viewer.searchColumn);

          if (filters.length === 0 && !search && !searchColumn) {
            this.setFeedback(viewer, 'Немає даних для збереження.');
            return;
          }

          const defaultName = this.generateDefaultFilterName(viewer.savedFilters.items);
          const name = await this.promptFilterName(defaultName);

          if (!name) {
            return;
          }

          viewer.savedFilters.loading = true;
          this.clearFeedback(viewer);

          try {
            const response = await this.sendFilterRequest('POST', 'store', 'content', tableName, {
              body: {
                name,
                filters,
                search,
                search_column: searchColumn,
              },
            });

            const normalized = normalizeSavedFiltersResponse(response || {});
            this.updateSavedFiltersState(viewer.savedFilters, normalized);
            this.setFeedback(viewer, 'Фільтр збережено.');

            if (normalized.lastUsed) {
              await this.applyContentSavedFilterById(normalized.lastUsed);
            }
          } catch (error) {
            this.setFeedback(viewer, error?.message ?? 'Не вдалося зберегти фільтр.');
          } finally {
            viewer.savedFilters.loading = false;
          }
        },
        async applySavedContentManagementFilter(filterId) {
          if (!filterId || !this.contentManagement.selectedTable || this.contentManagement.viewer.loading) {
            return;
          }

          await this.applyContentSavedFilterById(filterId, { markAsUsed: true, reload: true });
        },
        async deleteSavedContentManagementFilter(filterId) {
          if (!filterId) {
            return;
          }

          const tableName = typeof this.contentManagement.selectedTable === 'string'
            ? this.contentManagement.selectedTable.trim()
            : '';

          if (!tableName) {
            return;
          }

          if (!this.filterRoutes.destroy) {
            this.setFeedback(this.contentManagement.viewer, 'Видалення фільтрів недоступне.');
            return;
          }

          const state = this.contentManagement.viewer.savedFilters;
          state.loading = true;

          try {
            const response = await this.sendFilterRequest('DELETE', 'destroy', 'content', tableName, {
              filterId,
            });
            const normalized = normalizeSavedFiltersResponse(response || {});
            this.updateSavedFiltersState(state, normalized);

            if (state.lastUsed) {
              await this.applyContentSavedFilterById(state.lastUsed, { reload: true });
            }
          } catch (error) {
            this.setFeedback(this.contentManagement.viewer, error?.message ?? 'Не вдалося видалити фільтр.');
          } finally {
            state.loading = false;
          }
        },
        updateContentManagementSearch(value) {
          const viewer = this.contentManagement.viewer;
          const rawValue = typeof value === 'string' ? value : '';
          const normalized = rawValue.trim();

            viewer.searchInput = rawValue;

            if (!this.contentManagement.selectedTable) {
              viewer.search = normalized;
              return;
            }

            if (viewer.search === normalized && viewer.page === 1 && viewer.loaded) {
              return;
            }

            viewer.search = normalized;
            viewer.page = 1;

            this.loadContentManagementTable(this.contentManagement.selectedTable);
          },
          updateContentManagementSearchColumn(value) {
            const viewer = this.contentManagement.viewer;
            const normalized = typeof value === 'string' ? value.trim() : '';
            const previous = typeof viewer.searchColumn === 'string' ? viewer.searchColumn.trim() : '';

            if (!this.contentManagement.selectedTable) {
              viewer.searchColumn = normalized;
              return;
            }

            if (previous === normalized && viewer.page === 1 && viewer.loaded) {
              viewer.searchColumn = normalized;
              return;
            }

            viewer.searchColumn = normalized;
            viewer.page = 1;

            this.loadContentManagementTable(this.contentManagement.selectedTable);
          },
        async restoreContentManagementFilters(tableName, force = false, options = {}) {
          const viewer = this.contentManagement.viewer;

          if (!viewer) {
            return;
          }

          const normalized = typeof tableName === 'string' ? tableName.trim() : '';

          if (!force && viewer.restoredFromStorage && viewer.table === normalized) {
            return;
          }

          viewer.restoredFromStorage = true;
          viewer.table = normalized;

          if (!normalized) {
            viewer.savedFilters.loaded = true;
            return;
          }

          const result = await this.fetchSavedFiltersFor('content', normalized, viewer.savedFilters, { force });
          const opts = options && typeof options === 'object' ? options : {};
          const explicitFilterId = typeof opts.filterId === 'string' ? opts.filterId.trim() : '';
          const pendingFilterId = typeof this.contentManagement.pendingFilterId === 'string'
            ? this.contentManagement.pendingFilterId.trim()
            : '';
          const candidateFilterId = explicitFilterId || pendingFilterId;
          const markCandidate = opts.markAsUsed === true;
          const updateLastUsed = opts.updateLastUsed !== false;
          const defaultDisabled = result.defaultDisabled === true
            || viewer.savedFilters.defaultDisabled === true;
          let applied = false;

          const items = Array.isArray(viewer.savedFilters.items) ? viewer.savedFilters.items : [];
          const hasFilter = (filterId) => items.some((item) => item && item.id === filterId);

          const applyFilterById = async (filterId, applyOptions = {}) => {
            await this.applyContentSavedFilterById(filterId, {
              reload: false,
              markAsUsed: applyOptions.markAsUsed === true,
              updateLastUsed: applyOptions.updateLastUsed !== false,
            });
            applied = true;
          };

          if (candidateFilterId) {
            if (hasFilter(candidateFilterId)) {
              this.contentManagement.pendingFilterId = '';
              await applyFilterById(candidateFilterId, {
                markAsUsed: markCandidate,
                updateLastUsed,
              });
            } else {
              this.contentManagement.pendingFilterId = '';
            }
          }

          if (!applied && !defaultDisabled) {
            const defaultId = typeof viewer.savedFilters.defaultId === 'string'
              ? viewer.savedFilters.defaultId.trim()
              : '';

            if (defaultId && hasFilter(defaultId)) {
              await applyFilterById(defaultId, { markAsUsed: false, updateLastUsed: false });
            }
          }

          if (!applied && !defaultDisabled) {
            const lastUsedId = typeof result.lastUsed === 'string' ? result.lastUsed : '';

            if (lastUsedId && hasFilter(lastUsedId)) {
              await applyFilterById(lastUsedId, { markAsUsed: false });
            }
          }
        },
          async loadContentManagementTable(tableName) {
            const normalized = typeof tableName === 'string' ? tableName.trim() : '';

            if (!normalized) {
              return;
            }

            const viewer = this.contentManagement.viewer;
            const previousFilters = viewer.filters.map((filter) => ({ ...filter }));
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

              const sortColumn = typeof viewer.sort === 'string' ? viewer.sort.trim() : '';
              const sortDirection = viewer.direction === 'desc' ? 'desc' : 'asc';

              if (sortColumn) {
                url.searchParams.set('sort', sortColumn);
                url.searchParams.set('direction', sortDirection);
              }

              const searchQuery = typeof viewer.search === 'string' ? viewer.search.trim() : '';
              const searchColumn = typeof viewer.searchColumn === 'string' ? viewer.searchColumn.trim() : '';

              if (searchQuery) {
                url.searchParams.set('search', searchQuery);
              }

              if (searchColumn) {
                url.searchParams.set('search_column', searchColumn);
              }

              viewer.filters.forEach((filter, index) => {
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

              const relationOverrides = this.getContentManagementRelationOverrides(normalized);

              if (relationOverrides && typeof relationOverrides === 'object') {
                Object.entries(relationOverrides).forEach(([column, definition]) => {
                  const columnKey = typeof column === 'string' ? column.trim() : '';

                  if (!columnKey) {
                    return;
                  }

                  const payload = definition && typeof definition === 'object'
                    ? definition
                    : {};

                  let displayColumn = '';

                  if (typeof payload.column === 'string') {
                    displayColumn = payload.column.trim();
                  } else if (typeof definition === 'string') {
                    displayColumn = definition.trim();
                  }

                  if (!displayColumn) {
                    return;
                  }

                  url.searchParams.set(`display_relations[${columnKey}][column]`, displayColumn);

                  const relationTable = typeof payload.table === 'string' ? payload.table.trim() : '';

                  if (relationTable) {
                    url.searchParams.set(`display_relations[${columnKey}][table]`, relationTable);
                  }

                  const sourceColumn = typeof payload.source === 'string' ? payload.source.trim() : '';

                  if (sourceColumn) {
                    url.searchParams.set(`display_relations[${columnKey}][source]`, sourceColumn);
                  }
                });
              }

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

              const fallbackColumns = rows.length > 0
                ? Object.keys(rows[0]).filter((column) => column !== '__display')
                : [];

              const baseColumns = normalizedColumns.length > 0
                ? normalizedColumns
                : (structureColumns.length > 0
                  ? structureColumns
                  : fallbackColumns);

              viewer.columns = baseColumns;
              viewer.rows = rows.map((row) => (row && typeof row === 'object' ? row : {}));
              viewer.page = Number.isFinite(data.page) ? Number(data.page) : currentPage;
              viewer.perPage = Number.isFinite(data.per_page) ? Number(data.per_page) : currentPerPage;
              viewer.total = Number.isFinite(data.total) ? Number(data.total) : rows.length;
              viewer.lastPage = Number.isFinite(data.last_page)
                ? Math.max(1, Number(data.last_page))
                : Math.max(1, Math.ceil((viewer.total || 0) / (viewer.perPage || 1)));
              viewer.table = normalized;
              const responseSort = typeof data.sort === 'string' ? data.sort.trim() : '';
              const responseDirection = typeof data.direction === 'string'
                ? data.direction.toLowerCase()
                : '';
              viewer.sort = responseSort;
              viewer.direction = responseSort && responseDirection === 'desc' ? 'desc' : 'asc';
              if (viewer.sort && Array.isArray(viewer.columns) && !viewer.columns.includes(viewer.sort)) {
                viewer.sort = '';
                viewer.direction = 'asc';
              }
              const filtersFromResponse = Array.isArray(data.filters) ? data.filters : previousFilters;
              viewer.filters = this.normalizeFilters(filtersFromResponse, previousFilters);
              if (typeof data.search === 'string') {
                viewer.search = data.search.trim();
                viewer.searchInput = data.search;
              }
              const responseSearchColumn = typeof data.search_column === 'string'
                ? data.search_column.trim()
                : viewer.searchColumn;
              viewer.searchColumn = responseSearchColumn || '';
              if (
                viewer.searchColumn &&
                Array.isArray(viewer.columns) &&
                !viewer.columns.includes(viewer.searchColumn)
              ) {
                viewer.searchColumn = '';
              }
              this.ensureContentManagementVisibleColumns(normalized);
              viewer.loaded = true;
              this.ensureContentManagementFiltersOpen();
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
          async showContentManagementRecordValue(columnName, row) {
            const selected = typeof this.contentManagement.selectedTable === 'string'
              ? this.contentManagement.selectedTable.trim()
              : '';
            const normalizedColumn = typeof columnName === 'string' ? columnName.trim() : '';

            if (!selected || !normalizedColumn || !row || typeof row !== 'object') {
              return;
            }

            const baseTable = this.findTableByName(selected);
            const viewerColumns = Array.isArray(this.contentManagement.viewer.columns)
              ? this.contentManagement.viewer.columns
                .map((column) => (typeof column === 'string' ? column.trim() : ''))
                .filter((column) => column !== '')
              : [];
            const search = typeof this.contentManagement.viewer.search === 'string'
              ? this.contentManagement.viewer.search
              : '';
            const searchColumn = typeof this.contentManagement.viewer.searchColumn === 'string'
              ? this.contentManagement.viewer.searchColumn.trim()
              : '';

            const tableForState = baseTable
              ? {
                  ...baseTable,
                  records: {
                    ...(baseTable.records || {}),
                    columns: viewerColumns.length > 0
                      ? viewerColumns
                      : (Array.isArray(baseTable.records?.columns) ? baseTable.records.columns : []),
                    search,
                    searchColumn,
                  },
                }
              : {
                  name: selected,
                  structure: {
                    loading: false,
                    loaded: false,
                    columns: [],
                    error: null,
                  },
                  primaryKeys: [],
                  records: {
                    columns: viewerColumns,
                    search,
                    searchColumn,
                  },
                };

            const baseState = this.prepareValueModalBaseState(tableForState, normalizedColumn, row);

            if (!baseState) {
              return;
            }

            const tableForModal = baseTable || tableForState;

            if (baseState.identifiers.length === 0) {
              this.openValueModalFromRow(tableForModal, normalizedColumn, row, baseState);
              return;
            }

            await this.ensureStructureLoaded(tableForModal);

            const knownColumns = new Set(this.getTableColumnNames(tableForModal));

            if (!knownColumns.has(normalizedColumn)) {
              this.openValueModalFromRow(tableForModal, normalizedColumn, row, baseState);
              return;
            }

            await this.showRecordValue(tableForModal, normalizedColumn, row, baseState);
          },
          contentManagementCellValue(row, columnName) {
            const normalizedColumn = typeof columnName === 'string' ? columnName.trim() : '';

            if (!normalizedColumn || !row || typeof row !== 'object') {
              return undefined;
            }

            const displayMap = row.__display && typeof row.__display === 'object'
              ? row.__display
              : null;

            if (
              displayMap &&
              Object.prototype.hasOwnProperty.call(displayMap, normalizedColumn)
            ) {
              return displayMap[normalizedColumn];
            }

            return row[normalizedColumn];
          },
          contentManagementHighlight(columnName, row) {
            const value = this.contentManagementCellValue(row, columnName);
            const text = this.formatCell(value);
            const searchTerm = typeof this.contentManagement.viewer.search === 'string'
              ? this.contentManagement.viewer.search
              : '';
            const selectedColumn = typeof this.contentManagement.viewer.searchColumn === 'string'
              ? this.contentManagement.viewer.searchColumn.trim()
              : '';
            const currentColumn = typeof columnName === 'string' ? columnName.trim() : '';

            if (!searchTerm || (selectedColumn && selectedColumn !== currentColumn)) {
              return this.escapeHtml(text);
            }

            return this.highlightText(text, searchTerm);
          },
        syncBodyScrollLock() {
          const shouldLock =
            this.valueModal.open ||
            this.manualForeignModal.open ||
            this.filterNameModal.open ||
            this.contentManagement.deletionModal.open ||
            this.contentManagement.tableSettings.open;
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
          if (this.filterNameModal.open) {
            this.cancelFilterNameModal();
            return;
          }

          if (this.contentManagement.tableSettings.open) {
            this.closeContentManagementTableSettings();
            return;
          }

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
        findTableColumn(table, columnName) {
          if (!columnName || typeof columnName !== 'string') {
            return null;
          }

          const normalizedColumn = columnName.trim();

          if (!normalizedColumn) {
            return null;
          }

          const tableObject = typeof table === 'string' ? this.findTableByName(table) : table;

          if (!tableObject || !tableObject.structure || !Array.isArray(tableObject.structure.columns)) {
            return null;
          }

          return tableObject.structure.columns.find(
            (column) => column && column.name === normalizedColumn,
          ) ?? null;
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
        toggleRecordsFilters(table, state = null) {
          if (!table || !table.records) {
            return;
          }

          const nextState = typeof state === 'boolean' ? state : !table.records.filtersOpen;
          table.records.filtersOpen = nextState;
        },
        ensureRecordsFiltersOpen(table) {
          if (!table || !table.records) {
            return;
          }

          if (hasActiveRecordsFilters(table.records)) {
            table.records.filtersOpen = true;
          }
        },
        async toggleRecords(table) {
          table.records.visible = !table.records.visible;
          table.records.error = null;

          if (table.records.visible) {
            await this.restoreRecordsFilters(table);
          }

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
            this.ensureRecordsFiltersOpen(table);
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

          const sourceRow = row && typeof row === 'object' ? row : {};
          const identifiers = this.buildIdentifiers(table, sourceRow);

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
        prepareValueModalBaseState(table, columnName, row) {
          if (!table || typeof columnName !== 'string') {
            return null;
          }

          const normalizedColumn = columnName.trim();
          const tableName = typeof table.name === 'string' ? table.name : '';

          if (!normalizedColumn || !tableName) {
            return null;
          }

          const sourceRow = row && typeof row === 'object' ? row : {};
          const identifiers = this.buildIdentifiers(table, sourceRow);
          const sanitizedIdentifiers = Array.isArray(identifiers)
            ? identifiers
              .filter((identifier) => identifier && typeof identifier.column === 'string' && identifier.column !== '')
              .map((identifier) => ({
                column: identifier.column,
                value: identifier.value,
              }))
            : [];

          const records = table && typeof table === 'object' ? table.records || {} : {};
          const searchTerm = typeof records.search === 'string' ? records.search : '';
          const searchColumn = typeof records.searchColumn === 'string' ? records.searchColumn.trim() : '';

          return {
            tableName,
            columnName: normalizedColumn,
            identifiers: sanitizedIdentifiers,
            searchTerm: !searchTerm || (searchColumn && searchColumn !== normalizedColumn)
              ? ''
              : searchTerm,
          };
        },
        openValueModalFromRow(table, columnName, row, baseState = null) {
          const state = baseState || this.prepareValueModalBaseState(table, columnName, row);

          if (!state) {
            return;
          }

          const rawValue = row && typeof row === 'object'
            ? row[state.columnName]
            : null;

          this.valueModal.open = true;
          this.valueModal.loading = false;
          this.valueModal.error = null;
          this.valueModal.updateError = null;
          this.valueModal.table = state.tableName;
          this.valueModal.column = state.columnName;
          this.valueModal.value = this.formatCell(rawValue);
          this.valueModal.rawValue = rawValue;
          this.valueModal.editValue = this.prepareEditableValue(rawValue);
          this.valueModal.editing = false;
          this.valueModal.saving = false;
          this.valueModal.identifiers = state.identifiers;
          this.valueModal.searchTerm = state.searchTerm;
          this.valueModal.foreignKey = null;
          this.valueModal.foreignRecords = createForeignRecordsState();
          this.valueModal.canEdit = false;
          this.syncForeignSelectionWithRawValue(rawValue);
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
        async showRecordValue(table, column, row, baseState = null) {
          const state = baseState || this.prepareValueModalBaseState(table, column, row);

          if (!state) {
            return;
          }

          const fallbackRawValue = row && typeof row === 'object' ? row[state.columnName] : null;

          this.valueModal.open = true;
          this.valueModal.loading = true;
          this.valueModal.error = null;
          this.valueModal.updateError = null;
          this.valueModal.table = state.tableName;
          this.valueModal.column = state.columnName;
          this.valueModal.value = this.formatCell(fallbackRawValue);
          this.valueModal.rawValue = fallbackRawValue;
          this.valueModal.editValue = this.prepareEditableValue(fallbackRawValue);
          this.valueModal.editing = false;
          this.valueModal.saving = false;
          this.valueModal.identifiers = state.identifiers;
          this.valueModal.foreignKey = null;
          this.valueModal.foreignRecords = createForeignRecordsState();
          this.valueModal.searchTerm = state.searchTerm;
          this.valueModal.canEdit = state.identifiers.length > 0;

          if (state.identifiers.length === 0) {
            this.valueModal.loading = false;
            this.valueModal.error = 'Не вдалося визначити ідентифікатори запису для отримання значення.';
            return;
          }

          if (!this.recordsValueRoute) {
            this.valueModal.loading = false;
            this.valueModal.error = 'Маршрут для отримання значення не налаштовано.';
            return;
          }

          const modalTable = table && typeof table === 'object' ? table : null;

          if (modalTable) {
            await this.ensureStructureLoaded(modalTable);
          }

          const structureTable = await this.ensureStructureLoadedByName(state.tableName);
          const foreignSource = structureTable || modalTable;

          this.valueModal.foreignKey = foreignSource
            ? this.findForeignKey(foreignSource, state.columnName)
            : null;
          this.valueModal.foreignRecords = createForeignRecordsState();

          try {
            const url = new URL(
              this.recordsValueRoute.replace('__TABLE__', encodeURIComponent(state.tableName)),
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
                column: state.columnName,
                identifiers: state.identifiers,
              }),
            });

            if (!response.ok) {
              const payload = await response.json().catch(() => null);
              const message = payload?.message || 'Не вдалося отримати повне значення.';
              throw new Error(message);
            }

            const payload = await response.json();
            let rawValue = fallbackRawValue;

            if (payload && typeof payload === 'object' && payload !== null && Object.prototype.hasOwnProperty.call(payload, 'value')) {
              rawValue = payload.value;
            } else if (payload !== undefined) {
              rawValue = payload;
            }

            this.valueModal.rawValue = rawValue;
            this.valueModal.value = this.formatCell(rawValue);
            this.valueModal.editValue = this.prepareEditableValue(rawValue);
            this.syncForeignSelectionWithRawValue(rawValue);
          } catch (error) {
            this.valueModal.error = error.message ?? 'Сталася помилка під час отримання значення.';
            this.valueModal.rawValue = fallbackRawValue;
            this.valueModal.value = this.formatCell(fallbackRawValue);
            this.valueModal.editValue = this.prepareEditableValue(fallbackRawValue);
            this.syncForeignSelectionWithRawValue(fallbackRawValue);
          } finally {
            this.valueModal.loading = false;
          }
        },
        startEditingValue() {
          if (this.valueModal.loading || this.valueModal.error || !this.valueModal.canEdit) {
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
          this.valueModal.canEdit = true;
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

          table.records.filtersOpen = true;
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

          this.clearFeedback(table.records);
          table.records.filters = [];
          table.records.page = 1;
          this.loadRecords(table);
        },
        resolveRecordsSearchColumn(table, column) {
          const normalized = typeof column === 'string' ? column.trim() : '';

          if (!normalized) {
            return '';
          }

          const availableColumns = new Set(
            [
              ...(Array.isArray(table.records.columns) ? table.records.columns : []),
              ...this.getTableColumnNames(table),
            ]
              .map((item) => (typeof item === 'string' ? item.trim() : ''))
              .filter((item) => item !== ''),
          );

          if (availableColumns.size === 0 || availableColumns.has(normalized)) {
            return normalized;
          }

          return '';
        },
        applyRecordsSavedFilter(table, savedFilter) {
          if (!table || !savedFilter) {
            return;
          }

          const filters = Array.isArray(savedFilter.filters) ? savedFilter.filters : [];
          table.records.filters = this.normalizeFilters(filters, table.records.filters);

          const search = typeof savedFilter.search === 'string' ? savedFilter.search : '';
          table.records.search = search;
          table.records.searchInput = search;

          const searchColumn = this.resolveRecordsSearchColumn(table, savedFilter.searchColumn);
          table.records.searchColumn = searchColumn;

          table.records.page = 1;
          this.ensureRecordsFiltersOpen(table);
        },
        async applyRecordsSavedFilterById(table, filterId, options = {}) {
          if (!table || !filterId) {
            return;
          }

          const state = table.records.savedFilters;
          const target = Array.isArray(state.items)
            ? state.items.find((item) => item && item.id === filterId)
            : null;

          if (!target) {
            return;
          }

          this.applyRecordsSavedFilter(table, target);

          const shouldReload = options.reload === true;
          const shouldMark = options.markAsUsed === true;
          const updateLastUsed = options.updateLastUsed !== false;
          const tableName = typeof table.name === 'string' ? table.name.trim() : '';

          if (shouldMark && tableName) {
            try {
              const response = await this.sendFilterRequest('PATCH', 'use', 'records', tableName, {
                filterId,
              });
              const normalized = normalizeSavedFiltersResponse(response || {});
              if (normalized.lastUsed) {
                state.lastUsed = normalized.lastUsed;
              } else if (updateLastUsed) {
                state.lastUsed = filterId;
              }
            } catch (error) {
              if (updateLastUsed) {
                state.lastUsed = filterId;
              }
            }
          } else if (tableName && updateLastUsed) {
            state.lastUsed = filterId;
          }

          if (shouldReload) {
            await this.loadRecords(table);
          }
        },
        async saveRecordsFilters(table) {
          if (!table || table.records.loading) {
            return;
          }

          const tableName = typeof table.name === 'string' ? table.name.trim() : '';

          if (!tableName) {
            return;
          }

          if (!this.filterRoutes.store) {
            this.setFeedback(table.records, 'Збереження фільтрів недоступне.');
            return;
          }

          const filters = serializeFilters(table.records.filters);
          const search = typeof table.records.search === 'string' ? table.records.search.trim() : '';
          const searchColumn = this.resolveRecordsSearchColumn(table, table.records.searchColumn);

          if (filters.length === 0 && !search && !searchColumn) {
            this.setFeedback(table.records, 'Немає даних для збереження фільтру.');
            return;
          }

          const defaultName = this.generateDefaultFilterName(table.records.savedFilters.items);
          const name = await this.promptFilterName(defaultName);

          if (!name) {
            return;
          }

          table.records.savedFilters.loading = true;
          this.clearFeedback(table.records);

          try {
            const response = await this.sendFilterRequest('POST', 'store', 'records', tableName, {
              body: {
                name,
                filters,
                search,
                search_column: searchColumn,
              },
            });

            const normalized = normalizeSavedFiltersResponse(response || {});
            this.updateSavedFiltersState(table.records.savedFilters, normalized);
            this.setFeedback(table.records, 'Фільтр збережено.');

            if (normalized.lastUsed) {
              await this.applyRecordsSavedFilterById(table, normalized.lastUsed);
            }
          } catch (error) {
            this.setFeedback(table.records, error?.message ?? 'Не вдалося зберегти фільтр.');
          } finally {
            table.records.savedFilters.loading = false;
          }
        },
        async restoreRecordsFilters(table, force = false) {
          if (!table || !table.records) {
            return;
          }

          const state = table.records;

          if (!force && state.restoredFromStorage) {
            return;
          }

          state.restoredFromStorage = true;

          const tableName = typeof table.name === 'string' ? table.name.trim() : '';

          if (!tableName) {
            state.savedFilters.loaded = true;
            return;
          }

          const result = await this.fetchSavedFiltersFor('records', tableName, state.savedFilters, { force });
          const savedState = state.savedFilters;
          const defaultId = typeof savedState.defaultId === 'string' ? savedState.defaultId.trim() : '';
          const defaultDisabled = savedState.defaultDisabled === true
            || result.defaultDisabled === true;
          const items = Array.isArray(savedState.items) ? savedState.items : [];
          const hasFilter = (filterId) => items.some((item) => item && item.id === filterId);

          if (!defaultDisabled && defaultId && hasFilter(defaultId)) {
            await this.applyRecordsSavedFilterById(table, defaultId, { updateLastUsed: false });
            return;
          }

          if (defaultDisabled) {
            return;
          }

          const lastUsedId = typeof result.lastUsed === 'string' ? result.lastUsed : '';

          if (lastUsedId && hasFilter(lastUsedId)) {
            await this.applyRecordsSavedFilterById(table, lastUsedId);
          }
        },
        toggleRecordsDefaultFilter(table, filterId) {
          if (!table || !table.records || !table.records.savedFilters) {
            return;
          }

          const current = typeof table.records.savedFilters.defaultId === 'string'
            ? table.records.savedFilters.defaultId
            : '';

          if (current === filterId) {
            this.setRecordsDefaultFilter(table, '');
          } else {
            this.setRecordsDefaultFilter(table, filterId);
          }
        },
        async setRecordsDefaultFilter(table, filterId, options = {}) {
          if (!table || !table.records) {
            return;
          }

          const tableName = typeof table.name === 'string' ? table.name.trim() : '';

          if (!tableName) {
            return;
          }

          await this.updateSavedFilterDefault(
            'records',
            tableName,
            typeof filterId === 'string' ? filterId : '',
            table.records.savedFilters,
            table.records,
          );

          if (options && options.resetActive === true) {
            const records = table.records;

            if (records) {
              const savedFilters = records.savedFilters;

              if (savedFilters) {
                savedFilters.lastUsed = '';
              }

              records.filters = [];
              records.search = '';
              records.searchInput = '';
              records.searchColumn = '';
              records.page = 1;

              await this.loadRecords(table);
            }
          }
        },
        async applySavedRecordsFilterButton(table, filterId) {
          if (!table || !filterId || table.records.loading) {
            return;
          }

          await this.applyRecordsSavedFilterById(table, filterId, { markAsUsed: true });
          table.records.page = 1;
          await this.loadRecords(table);
        },
        async deleteSavedRecordsFilter(table, filterId) {
          if (!table || !filterId) {
            return;
          }

          if (!this.filterRoutes.destroy) {
            this.setFeedback(table.records, 'Видалення фільтрів недоступне.');
            return;
          }

          const tableName = typeof table.name === 'string' ? table.name.trim() : '';

          if (!tableName) {
            return;
          }

          const state = table.records.savedFilters;
          state.loading = true;

          try {
            const response = await this.sendFilterRequest('DELETE', 'destroy', 'records', tableName, {
              filterId,
            });
            const normalized = normalizeSavedFiltersResponse(response || {});
            this.updateSavedFiltersState(state, normalized);

            if (state.lastUsed) {
              await this.applyRecordsSavedFilterById(table, state.lastUsed, { reload: true });
            }
          } catch (error) {
            this.setFeedback(table.records, error?.message ?? 'Не вдалося видалити фільтр.');
          } finally {
            state.loading = false;
          }
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
        setFeedback(target, message) {
          if (!target || typeof target !== 'object') {
            return;
          }

          if (target.feedbackTimeout) {
            clearTimeout(target.feedbackTimeout);
            target.feedbackTimeout = null;
          }

          target.feedback = message || '';

          if (target.feedback) {
            target.feedbackTimeout = window.setTimeout(() => {
              target.feedback = '';
              target.feedbackTimeout = null;
            }, 4000);
          }
        },
        clearFeedback(target) {
          if (!target || typeof target !== 'object') {
            return;
          }

          if (target.feedbackTimeout) {
            clearTimeout(target.feedbackTimeout);
            target.feedbackTimeout = null;
          }

          target.feedback = '';
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
        globalSearchRowEntries(row) {
          if (!row || typeof row !== 'object') {
            return [];
          }

          const entries = [];

          Object.entries(row).forEach(([key, value]) => {
            if (key === '__display' && value && typeof value === 'object') {
              Object.entries(value).forEach(([displayKey, displayValue]) => {
                entries.push({
                  key: displayKey,
                  value: this.formatCell(displayValue),
                });
              });

              return;
            }

            if (typeof key === 'string' && key.startsWith('__')) {
              return;
            }

            entries.push({
              key,
              value: this.formatCell(value),
            });
          });

          return entries;
        },
        async performGlobalKeywordSearch() {
          if (!this.keywordSearchRoute) {
            this.globalSearch.error = 'Маршрут пошуку недоступний.';
            return;
          }

          const keyword = typeof this.globalSearch.keyword === 'string' ? this.globalSearch.keyword.trim() : '';

          if (!keyword) {
            this.globalSearch.results = [];
            this.globalSearch.error = '';
            this.globalSearch.completed = false;
            return;
          }

          const limit = Number(this.globalSearch.limit);
          const perTable = Number.isFinite(limit) && limit > 0 ? Math.min(Math.trunc(limit), 25) : 5;
          const requestId = Date.now();

          this.globalSearch.requestId = requestId;
          this.globalSearch.loading = true;
          this.globalSearch.error = '';
          this.globalSearch.completed = false;

          const params = new URLSearchParams({
            keyword,
            per_table: String(perTable),
          });

          try {
            const response = await fetch(`${this.keywordSearchRoute}?${params.toString()}`, {
              headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
              },
            });

            if (!response.ok) {
              let message = response.statusText || 'Помилка запиту.';

              try {
                const payload = await response.json();
                if (payload && typeof payload.message === 'string') {
                  message = payload.message;
                }
              } catch (error) {
                // ignore JSON parse errors
              }

              throw new Error(message);
            }

            const payload = await response.json();

            if (this.globalSearch.requestId !== requestId) {
              return;
            }

            this.globalSearch.results = Array.isArray(payload.results) 
              ? payload.results
                  .filter(result => result && typeof result === 'object')
                  .map(result => ({ ...result, open: true }))
              : [];
            this.globalSearch.completed = true;
          } catch (error) {
            if (this.globalSearch.requestId !== requestId) {
              return;
            }

            this.globalSearch.error = error && error.message ? error.message : 'Не вдалося виконати пошук.';
            this.globalSearch.results = [];
            this.globalSearch.completed = false;
          } finally {
            if (this.globalSearch.requestId === requestId) {
              this.globalSearch.loading = false;
            }
          }
        },
        clearGlobalKeywordSearch() {
          this.globalSearch.keyword = '';
          this.globalSearch.results = [];
          this.globalSearch.error = '';
          this.globalSearch.completed = false;
          this.globalSearch.requestId = Date.now();
          this.globalSearch.loading = false;
        },
        toggleGlobalSearchResult(result) {
          if (result && typeof result === 'object') {
            result.open = !result.open;
          }
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
