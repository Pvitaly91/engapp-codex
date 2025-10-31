@extends('layouts.app')

@section('title', 'Content Management')

@section('content')
  <div
    class="space-y-8"
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
      @js(route('database-structure.content-management.menu.store')),
      @js($contentManagementMenu),
    )"
    x-init="activeTab = 'content-management'; ensureContentManagementData();"
    @keydown.window.escape.prevent="handleEscape()"
  >
    <header class="rounded-3xl border border-border/70 bg-card/80 p-6 shadow-soft">
      <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
        <div class="space-y-3">
          <p class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-widest text-muted-foreground">
            <span class="inline-flex h-2.5 w-2.5 rounded-full bg-primary"></span>
            Інструмент адміністрування
          </p>
          <div class="space-y-2">
            <h1 class="text-3xl font-semibold text-foreground">Content Management</h1>
            <p class="max-w-2xl text-sm text-muted-foreground">
              Керуйте записами вибраних таблиць через зручний інтерфейс з фільтрами, пошуком та попереднім переглядом.
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
          Використовуйте меню зліва, щоб обрати таблицю для роботи.
        </div>
        <div class="flex w-full flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
          <a
            href="{{ route('database-structure.index') }}"
            class="inline-flex items-center gap-2 rounded-full border border-border/70 bg-background px-4 py-2 text-xs font-semibold text-muted-foreground transition hover:border-primary/40 hover:text-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
          >
            <i class="fa-solid fa-database text-[11px]"></i>
            Структура БД
          </a>
        </div>
      </div>
    </header>

      <div class="mt-6 rounded-3xl border border-border/70 bg-card shadow-soft">
        <div class="grid gap-0 lg:grid-cols-[260px,1fr]">
          <aside class="border-b border-border/60 bg-background/60 p-6 lg:border-b-0 lg:border-r">
            <div class="flex items-center justify-between gap-2">
              <h2 class="text-sm font-semibold uppercase tracking-wide text-muted-foreground">Меню таблиць</h2>
              <div class="flex items-center gap-2">
                <span class="rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary" x-text="contentManagement.menu.length + ' шт.'"></span>
                <button
                  type="button"
                  class="inline-flex items-center gap-1 rounded-full border border-primary/40 bg-primary/10 px-3 py-1 text-[11px] font-semibold text-primary transition hover:bg-primary/20 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 disabled:cursor-not-allowed disabled:opacity-60"
                  :disabled="!contentManagementMenuRoutes.store"
                  @click="openContentManagementMenuModal()"
                >
                  <i class="fa-solid fa-plus text-[9px]"></i>
                  <span>Додати таблицю</span>
                </button>
              </div>
            </div>
            <template x-if="contentManagement.menu.length === 0">
              <p class="mt-4 rounded-2xl border border-dashed border-border/60 bg-muted/20 p-4 text-xs text-muted-foreground">
                Меню порожнє. Додайте записи у конфігурації
                <code class="rounded bg-background px-1 text-[11px] text-foreground">database-structure.content_management.menu</code>.
              </p>
            </template>
            <template x-if="contentManagement.menu.length > 0">
              <nav class="mt-4 space-y-2">
                <template x-for="item in contentManagement.menu" :key="item.key">
                  <button
                    type="button"
                    class="flex w-full flex-col gap-1 rounded-2xl border px-4 py-3 text-left transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
                    :class="contentManagement.activeKey === item.key ? 'border-primary/50 bg-primary/10 text-primary shadow-soft/40' : 'border-border/60 bg-background text-muted-foreground hover:border-primary/40 hover:text-primary'"
                    @click="selectContentMenuItem(item.key)"
                  >
                    <span class="text-sm font-semibold" x-text="item.label"></span>
                    <span class="text-xs text-muted-foreground" x-show="item.description" x-text="item.description"></span>
                    <span class="mt-2 inline-flex items-center gap-2 text-[11px] text-muted-foreground/80">
                      <i class="fa-solid fa-table text-[10px]"></i>
                      <span x-text="item.table"></span>
                    </span>
                  </button>
                </template>
              </nav>
            </template>
          </aside>
          <section class="p-6">
            <template x-if="contentManagement.menu.length === 0">
              <div class="rounded-2xl border border-dashed border-border/60 bg-muted/20 p-6 text-sm text-muted-foreground">
                Налаштуйте меню ліворуч, щоб обрати таблиці для керування контентом.
              </div>
            </template>
            <template x-if="contentManagement.menu.length > 0 && !contentManagementActiveTable && !contentManagementActiveItem">
              <div class="rounded-2xl border border-dashed border-border/60 bg-muted/20 p-6 text-sm text-muted-foreground">
                Виберіть таблицю з меню ліворуч, щоб переглянути записи.
              </div>
            </template>
            <template x-if="contentManagement.menu.length > 0 && contentManagementActiveItem && !contentManagementActiveTable">
              <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-sm text-amber-700">
                Таблицю
                <span class="font-semibold text-amber-800" x-text="contentManagementActiveItem.table"></span>
                не знайдено у структурі бази даних. Перевірте налаштування меню.
              </div>
            </template>
            <template x-if="contentManagementActiveTable">
              <div
                x-data="{ table: contentManagementActiveTable }"
                x-effect="table = $root.contentManagementActiveTable"
                class="space-y-6"
              >
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                  <div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">
                      Content Management
                    </div>
                    <h2 class="mt-3 text-2xl font-semibold text-foreground" x-text="table.name"></h2>
                    <p class="mt-1 text-sm text-muted-foreground" x-show="table.comment" x-text="table.comment"></p>
                  </div>
                  <div class="flex flex-wrap items-center gap-2">
                    <button
                      type="button"
                      class="inline-flex items-center gap-2 rounded-full border border-border/60 bg-background px-4 py-2 text-xs font-semibold text-muted-foreground transition hover:border-primary/60 hover:text-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
                      @click.prevent="$root.loadRecords(table)"
                      :disabled="table.records.loading"
                    >
                      <i class="fa-solid fa-rotate-right text-[10px]"></i>
                      Оновити записи
                    </button>
                    <button
                      type="button"
                      class="inline-flex items-center gap-2 rounded-full border border-border/60 bg-background px-4 py-2 text-xs font-semibold text-muted-foreground transition hover:border-primary/60 hover:text-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
                      @click.prevent="$root.ensureStructureLoaded(table)"
                    >
                      <i class="fa-solid fa-table-columns text-[10px]"></i>
                      Оновити структуру
                    </button>
                  </div>
                </div>

                <div class="rounded-2xl border border-border/60 bg-muted/20 p-4 text-[15px] text-muted-foreground">
                  <div class="flex flex-col gap-4">
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                      <h3 class="text-sm font-semibold text-foreground">Фільтри записів</h3>
                      <div class="flex flex-wrap items-center gap-2 text-[15px]">
                        <button
                          type="button"
                          class="inline-flex items-center gap-2 rounded-full border border-border/70 bg-background px-4 py-1.5 text-[15px] font-semibold text-foreground transition hover:border-primary/60 hover:text-primary"
                          @click.stop="$root.addFilter(table)"
                          :disabled="table.records.loading"
                        >
                          <i class="fa-solid fa-plus text-[10px]"></i>
                          Додати фільтр
                        </button>
                        <button
                          type="button"
                          class="inline-flex items-center gap-2 rounded-full border border-border/70 bg-background px-4 py-1.5 text-[15px] font-semibold text-foreground transition hover:border-primary/60 hover:text-primary disabled:cursor-not-allowed disabled:opacity-60"
                          :disabled="table.records.filters.length === 0 || table.records.loading"
                          @click.stop="$root.resetFilters(table)"
                        >
                          <i class="fa-solid fa-rotate-left text-[10px]"></i>
                          Скинути
                        </button>
                        <button
                          type="button"
                          class="inline-flex items-center gap-2 rounded-full border border-primary/40 bg-primary/10 px-4 py-1.5 text-[15px] font-semibold text-primary transition hover:bg-primary/20 disabled:cursor-not-allowed disabled:opacity-60"
                          :disabled="table.records.loading"
                          @click.stop="$root.applyFilters(table)"
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
                            @input.debounce.500ms="$root.updateSearch(table, $event.target.value)"
                          />
                        </div>
                        <label class="flex flex-col gap-1 text-[12px] font-semibold uppercase tracking-wide text-muted-foreground sm:w-48">
                          <span>Колонка для пошуку</span>
                          <select
                            class="rounded-xl border border-input bg-background px-3 py-2 text-[15px] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40 disabled:cursor-not-allowed disabled:opacity-75"
                            :disabled="table.records.loading || !table.records.columns || table.records.columns.length === 0"
                            :value="table.records.searchColumn"
                            @change="$root.updateSearchColumn(table, $event.target.value)"
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
                              <template x-for="option in $root.filterOperators" :key="option.value">
                                <option :value="option.value" x-text="option.label"></option>
                              </template>
                            </select>
                          </label>
                          <label
                            class="flex flex-1 flex-col gap-1 text-[15px] font-semibold uppercase tracking-wide text-muted-foreground/80"
                            x-show="$root.operatorRequiresValue(filter.operator)"
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
                          @click.stop="$root.removeFilter(table, filterIndex)"
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
                            @change="$root.changePerPage(table, $event.target.value)"
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
                            @click="$root.changePage(table, table.records.page - 1)"
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
                            @click="$root.changePage(table, table.records.page + 1)"
                          >
                            Наступна
                            <i class="fa-solid fa-chevron-right text-[10px]"></i>
                          </button>
                        </div>
                      </div>
                    </div>

                    <div class="overflow-x-auto">
                      <table class="min-w-full divide-y divide-border/60 text-sm">
                        <thead class="bg-muted/30 text-xs uppercase tracking-wide text-muted-foreground">
                          <tr>
                            <template x-for="column in table.records.columns" :key="column + '-header'">
                              <th class="whitespace-nowrap px-4 py-3 font-semibold">
                                <button
                                  type="button"
                                  class="inline-flex items-center gap-1 text-left text-xs font-semibold uppercase tracking-wide transition hover:text-primary"
                                  @click.stop="$root.toggleSort(table, column)"
                                >
                                  <span x-text="column"></span>
                                  <template x-if="table.records.sort === column">
                                    <i class="fa-solid" :class="table.records.direction === 'asc' ? 'fa-arrow-up' : 'fa-arrow-down'"></i>
                                  </template>
                                </button>
                              </th>
                            </template>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide">Дії</th>
                          </tr>
                        </thead>
                        <tbody class="divide-y divide-border/60 bg-white text-sm">
                          <template x-for="(row, rowIndex) in table.records.rows" :key="rowIndex">
                            <tr class="hover:bg-muted/20">
                              <template x-for="column in table.records.columns" :key="rowIndex + '-' + column">
                                <td class="whitespace-nowrap px-4 py-2 align-top text-sm text-foreground">
                                  <div class="space-y-1">
                                    <div class="font-medium text-foreground" x-html="$root.formatCell(row[column])"></div>
                                    <button
                                      type="button"
                                      class="inline-flex items-center gap-2 text-[11px] font-semibold text-primary transition hover:underline"
                                      @click.stop="$root.openValueModal(table, column, row)"
                                    >
                                      <i class="fa-solid fa-maximize text-[10px]"></i>
                                      Переглянути
                                    </button>
                                  </div>
                                </td>
                              </template>
                              <td class="whitespace-nowrap px-4 py-2 text-right text-sm">
                                <div class="flex flex-col gap-2 sm:inline-flex sm:flex-row">
                                  <button
                                    type="button"
                                    class="inline-flex items-center gap-2 rounded-full border border-border/60 bg-background px-3 py-1 text-xs font-medium text-muted-foreground transition hover:border-primary/60 hover:text-primary disabled:cursor-not-allowed disabled:opacity-60"
                                    :disabled="table.records.loading"
                                    @click.stop="$root.previewRecord(table, row)"
                                  >
                                    <i class="fa-solid fa-eye text-[10px]"></i>
                                    Переглянути
                                  </button>
                                  <button
                                    type="button"
                                    class="inline-flex items-center gap-2 rounded-full border border-border/60 bg-background px-3 py-1 text-xs font-medium text-muted-foreground transition hover:border-primary/60 hover:text-primary disabled:cursor-not-allowed disabled:opacity-60"
                                    :disabled="table.records.loading"
                                    @click.stop="$root.editRecord(table, row)"
                                  >
                                    <i class="fa-solid fa-pen text-[10px]"></i>
                                    Редагувати
                                  </button>
                                  <button
                                    type="button"
                                    class="inline-flex items-center gap-2 rounded-full border border-border/60 bg-background px-3 py-1 text-xs font-medium text-rose-600 transition hover:border-rose-300 hover:text-rose-500 disabled:cursor-not-allowed disabled:opacity-60"
                                    :disabled="table.records.loading || table.records.deletingRowIndex === rowIndex"
                                    @click.stop="$root.deleteRecord(table, row, rowIndex)"
                                  >
                                    <span x-show="table.records.deletingRowIndex !== rowIndex">Видалити</span>
                                    <span x-show="table.records.deletingRowIndex === rowIndex" x-cloak>Видалення...</span>
                                  </button>
                                </div>
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
                            @change="$root.changePerPage(table, $event.target.value)"
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
                            @click="$root.changePage(table, table.records.page - 1)"
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
                            @click="$root.changePage(table, table.records.page + 1)"
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
            </template>
          </section>
        </div>
      </div>

    @include('database-structure::partials.modals')
  </div>
@endsection

@include('database-structure::partials.viewer-script')

