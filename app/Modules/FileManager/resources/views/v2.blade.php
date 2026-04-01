@extends('layouts.app')

@section('title', 'Файловий менеджер V2')

@section('content')
<div
    class="mx-auto px-4 pb-10"
    x-data="fileManagerV2(@js($initialPath ?? ''), @js($initialSelection ?? ''), @js($initialTarget ?? ''))"
    x-init="init()"
    @keydown.window.ctrl.s.prevent="saveCurrentFile()"
    @keydown.window.meta.s.prevent="saveCurrentFile()"
>
    <div class="mb-6 flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
        <div class="space-y-2">
            <div class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-cyan-300">
                <span class="h-2 w-2 rounded-full bg-cyan-300"></span>
                V2
            </div>
            <h1 class="text-3xl font-bold text-slate-900">Файловий менеджер V2</h1>
            <p class="max-w-3xl text-sm text-slate-600">
                Окремий URL для кожного файлу або папки, редагування текстових файлів з підсвіткою синтаксису,
                створення нових файлів і директорій, видалення та швидка навігація по проекту.
            </p>
            <p class="text-xs text-slate-500">
                Базова директорія:
                <code class="rounded bg-slate-100 px-2 py-1">{{ $basePath }}</code>
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a
                href="{{ route('file-manager.index') }}"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:bg-slate-50"
            >
                <i class="fa-solid fa-arrow-left"></i>
                Старий менеджер
            </a>
            <button
                type="button"
                @click="copyCurrentUrl()"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:bg-slate-50"
            >
                <i class="fa-regular fa-copy"></i>
                Копіювати URL
            </button>
            <button
                type="button"
                @click="refreshCurrentDirectory()"
                class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-800"
            >
                <i class="fa-solid fa-rotate"></i>
                Оновити
            </button>
        </div>
    </div>

    <div
        x-show="notice.message"
        x-cloak
        class="mb-6 rounded-2xl border px-4 py-3 text-sm shadow-sm"
        :class="notice.type === 'error'
            ? 'border-red-200 bg-red-50 text-red-700'
            : 'border-emerald-200 bg-emerald-50 text-emerald-700'"
    >
        <div class="flex items-start gap-3">
            <i class="mt-0.5" :class="notice.type === 'error' ? 'fa-solid fa-circle-exclamation' : 'fa-solid fa-circle-check'"></i>
            <span x-text="notice.message"></span>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[360px_minmax(0,1fr)]">
        <aside class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-soft">
            <div class="border-b border-slate-200 bg-gradient-to-br from-slate-50 via-white to-cyan-50/70 p-5">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">Навігація</p>
                        <h2 class="mt-2 text-xl font-semibold text-slate-900">Каталог</h2>
                    </div>
                    <button
                        type="button"
                        @click="goToRoot()"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold uppercase tracking-[0.16em] text-slate-600 transition hover:border-slate-300 hover:bg-slate-50"
                    >
                        <i class="fa-solid fa-house"></i>
                        Root
                    </button>
                </div>

                <div class="mt-4 flex flex-wrap gap-2">
                    <button
                        type="button"
                        @click="toggleCreate('file')"
                        class="inline-flex items-center gap-2 rounded-xl bg-cyan-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-cyan-700"
                    >
                        <i class="fa-regular fa-file-lines"></i>
                        Новий файл
                    </button>
                    <button
                        type="button"
                        @click="toggleCreate('directory')"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:bg-slate-50"
                    >
                        <i class="fa-regular fa-folder"></i>
                        Нова папка
                    </button>
                </div>

                <div
                    x-show="createMode"
                    x-cloak
                    class="mt-4 rounded-2xl border border-slate-200 bg-white/90 p-4"
                >
                    <label class="block text-sm font-medium text-slate-700" x-text="createMode === 'file' ? 'Новий файл' : 'Нова папка'"></label>
                    <input
                        type="text"
                        x-model="createName"
                        @keydown.enter.prevent="submitCreate()"
                        class="mt-2 w-full rounded-xl border-slate-300 text-sm focus:border-cyan-500 focus:ring-cyan-500"
                        :placeholder="createMode === 'file' ? 'наприклад: app/Example.php або notes.md' : 'наприклад: resources/new-folder'"
                    >
                    <p class="mt-2 text-xs text-slate-500">
                        Шлях:
                        <code class="rounded bg-slate-100 px-2 py-1" x-text="buildPath(currentPath, createName) || '/'"></code>
                    </p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <button
                            type="button"
                            @click="submitCreate()"
                            :disabled="createLoading || !createName.trim()"
                            class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            <i class="fa-solid fa-plus"></i>
                            <span x-text="createLoading ? 'Створення...' : 'Створити'"></span>
                        </button>
                        <button
                            type="button"
                            @click="cancelCreate()"
                            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:bg-slate-50"
                        >
                            Скасувати
                        </button>
                    </div>
                </div>

                <div class="mt-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Breadcrumb</p>
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <template x-for="crumb in breadcrumbItems()" :key="crumb.path || '__root__'">
                            <button
                                type="button"
                                @click="openDirectory(crumb.path)"
                                class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:border-slate-300 hover:bg-slate-50"
                                :class="crumb.path === currentPath ? 'border-cyan-300 bg-cyan-50 text-cyan-700' : ''"
                                x-text="crumb.label"
                            ></button>
                        </template>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Фільтр</label>
                    <div class="relative mt-2">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                        <input
                            type="text"
                            x-model="filterTerm"
                            class="w-full rounded-xl border-slate-300 pl-9 text-sm focus:border-cyan-500 focus:ring-cyan-500"
                            placeholder="Назва файлу або папки"
                        >
                    </div>
                </div>
            </div>

            <div class="max-h-[78vh] overflow-y-auto p-3">
                <template x-if="treeLoading">
                    <div class="flex flex-col items-center justify-center gap-3 py-16 text-slate-500">
                        <i class="fa-solid fa-spinner animate-spin text-2xl"></i>
                        <p class="text-sm">Завантаження каталогу...</p>
                    </div>
                </template>

                <template x-if="!treeLoading && visibleTreeNodes.length === 0">
                    <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-10 text-center text-sm text-slate-500">
                        Дерево файлів ще не завантажене або фільтр нічого не знайшов.
                    </div>
                </template>

                <div x-show="!treeLoading" x-cloak class="space-y-1">
                    <template x-for="node in visibleTreeNodes" :key="node.path || '__root__'">
                        <div
                            class="group rounded-2xl border border-slate-200 bg-white transition hover:border-slate-300 hover:shadow-sm"
                            :class="activeTargetPath === node.path ? 'border-cyan-300 bg-cyan-50/70 shadow-sm' : ''"
                        >
                            <div class="flex items-center gap-2 p-2.5" :style="`padding-left: ${12 + (node.depth * 18)}px`">
                                <button
                                    type="button"
                                    class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                                    :class="node.type !== 'directory' ? 'opacity-0 pointer-events-none' : ''"
                                    @click.stop="toggleTreeNode(node)"
                                >
                                    <i
                                        class="fa-solid text-[11px]"
                                        :class="isTreeExpanded(node.path) ? 'fa-chevron-down' : 'fa-chevron-right'"
                                    ></i>
                                </button>

                                <button
                                    type="button"
                                    @click="node.type === 'directory' ? openDirectory(node.path) : openFile(node.path)"
                                    class="flex min-w-0 flex-1 items-center gap-3 text-left"
                                >
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-base"
                                        :class="node.type === 'directory' ? 'bg-amber-100 text-amber-600' : 'bg-slate-100 text-slate-600'">
                                        <i :class="node.type === 'directory' ? (isTreeExpanded(node.path) ? 'fa-regular fa-folder-open' : 'fa-regular fa-folder') : getFileIcon(node)"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-slate-900" x-text="node.name"></p>
                                        <p class="mt-0.5 truncate text-[11px] text-slate-500">
                                            <span x-text="node.type === 'directory' ? 'Папка' : formatFileSize(node.size)"></span>
                                            <span x-show="node.type === 'file' && node.extension">
                                                •
                                                <span x-text="node.extension"></span>
                                            </span>
                                        </p>
                                    </div>
                                </button>

                                <div class="flex shrink-0 items-center gap-1 text-slate-400 transition group-hover:text-slate-600">
                                    <button
                                        type="button"
                                        @click.stop="copyTargetUrl(node.path)"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-xl transition hover:bg-white hover:text-slate-900"
                                        title="Копіювати URL"
                                    >
                                        <i class="fa-regular fa-copy"></i>
                                    </button>
                                    <button
                                        type="button"
                                        @click.stop="node.type === 'directory' ? openDirectory(node.path) : openFile(node.path)"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-xl transition hover:bg-white hover:text-slate-900"
                                        :title="node.type === 'directory' ? 'Відкрити папку' : 'Відкрити файл'"
                                    >
                                        <i :class="node.type === 'directory' ? 'fa-regular fa-folder-open' : 'fa-regular fa-pen-to-square'"></i>
                                    </button>
                                    <button
                                        type="button"
                                        @click.stop="confirmDelete(node)"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-xl transition hover:bg-red-50 hover:text-red-600"
                                        title="Видалити"
                                    >
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </aside>

        <section class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-soft">
            <div class="border-b border-slate-200 bg-gradient-to-br from-slate-50 via-white to-slate-100/70 p-4">
                <template x-if="activeItem">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500" x-text="activeItem.type === 'directory' ? 'Папка' : 'Файл'"></p>
                            <h2 class="mt-1.5 text-2xl font-semibold text-slate-900" x-text="activeItem.name || '/'"></h2>
                            <p class="mt-1 break-all font-mono text-sm text-slate-500" x-text="activeItem.path || '/'"></p>
                            <div class="mt-2 flex flex-wrap gap-2 text-xs text-slate-500">
                                <span class="rounded-full bg-slate-100 px-3 py-1" x-show="activeItem.type === 'directory'">
                                    <span x-text="`${items.length} елемент(ів)`"></span>
                                </span>
                                <span class="rounded-full bg-slate-100 px-3 py-1" x-show="activeItem.type === 'file' && fileData">
                                    <span x-text="formatFileSize(fileData?.size || activeItem.size || 0)"></span>
                                </span>
                                <span class="rounded-full bg-slate-100 px-3 py-1" x-show="activeItem.type === 'file' && fileData?.mime_type">
                                    <span x-text="fileData?.mime_type"></span>
                                </span>
                                <span class="rounded-full bg-slate-100 px-3 py-1" x-show="activeItem.type === 'file' && activeSyntaxLabel()">
                                    <span x-text="activeSyntaxLabel()"></span>
                                </span>
                                <span class="rounded-full bg-slate-100 px-3 py-1" x-show="activeItem.modified">
                                    <span x-text="formatDate(activeItem.modified)"></span>
                                </span>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <button
                                type="button"
                                @click="copyCurrentUrl()"
                                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:bg-slate-50"
                            >
                                <i class="fa-regular fa-copy"></i>
                                Копіювати URL
                            </button>
                            <button
                                type="button"
                                x-show="activeItem.type === 'directory'"
                                @click="toggleCreate('file')"
                                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:bg-slate-50"
                            >
                                <i class="fa-regular fa-file-lines"></i>
                                Файл тут
                            </button>
                            <button
                                type="button"
                                x-show="activeItem.type === 'file'"
                                @click="downloadActive()"
                                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:bg-slate-50"
                            >
                                <i class="fa-solid fa-download"></i>
                                Завантажити
                            </button>
                            <button
                                type="button"
                                @click="confirmDelete(activeItem)"
                                class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-red-700"
                            >
                                <i class="fa-regular fa-trash-can"></i>
                                Видалити
                            </button>
                        </div>
                    </div>
                </template>

                <template x-if="!activeItem">
                    <div class="py-6">
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">Готово до роботи</p>
                        <h2 class="mt-2 text-2xl font-semibold text-slate-900">Відкрий файл або папку</h2>
                        <p class="mt-2 max-w-2xl text-sm text-slate-600">
                            Вибери елемент у лівій колонці. Для текстових файлів редактор відкриється одразу, а URL у браузері зміниться на персональне посилання цього файлу.
                        </p>
                    </div>
                </template>
            </div>

            <div class="p-4">
                <template x-if="!activeItem">
                    <div class="rounded-[24px] border border-dashed border-slate-200 bg-slate-50 px-6 py-12 text-center text-slate-500">
                        <i class="fa-regular fa-folder-open text-4xl text-slate-300"></i>
                        <p class="mt-4 text-sm">Ліва панель використовується для навігації та відкриття файлів.</p>
                    </div>
                </template>

                <template x-if="activeItem && activeItem.type === 'directory'">
                    <div class="space-y-4">
                        <div class="rounded-[24px] border border-slate-200 bg-slate-50 px-6 py-6">
                            <p class="text-sm font-medium text-slate-700">Відкрита папка</p>
                            <p class="mt-2 text-sm text-slate-600">
                                Зараз показано вміст каталогу
                                <code class="rounded bg-white px-2 py-1 font-mono text-xs" x-text="currentPath || '/'"></code>.
                                Створи новий файл або папку, або відкрий будь-який файл ліворуч.
                            </p>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <button
                                type="button"
                                @click="toggleCreate('file')"
                                class="flex items-center gap-3 rounded-[24px] border border-slate-200 bg-white px-5 py-4 text-left transition hover:border-cyan-300 hover:bg-cyan-50"
                            >
                                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-cyan-100 text-cyan-700">
                                    <i class="fa-regular fa-file-lines text-lg"></i>
                                </span>
                                <span>
                                    <span class="block text-sm font-semibold text-slate-900">Створити файл</span>
                                    <span class="block text-xs text-slate-500">Новий текстовий файл у поточній папці</span>
                                </span>
                            </button>

                            <button
                                type="button"
                                @click="toggleCreate('directory')"
                                class="flex items-center gap-3 rounded-[24px] border border-slate-200 bg-white px-5 py-4 text-left transition hover:border-slate-300 hover:bg-slate-50"
                            >
                                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-100 text-amber-700">
                                    <i class="fa-regular fa-folder text-lg"></i>
                                </span>
                                <span>
                                    <span class="block text-sm font-semibold text-slate-900">Створити папку</span>
                                    <span class="block text-xs text-slate-500">Нова директорія в межах поточного каталогу</span>
                                </span>
                            </button>
                        </div>
                    </div>
                </template>

                <template x-if="activeItem && activeItem.type === 'file'">
                    <div class="space-y-4">
                        <template x-if="fileLoading">
                            <div class="flex flex-col items-center justify-center gap-3 rounded-[24px] border border-slate-200 bg-slate-50 px-6 py-16 text-slate-500">
                                <i class="fa-solid fa-spinner animate-spin text-2xl"></i>
                                <p class="text-sm">Завантаження файлу...</p>
                            </div>
                        </template>

                        <template x-if="!fileLoading && fileError">
                            <div class="rounded-[24px] border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
                                <div class="flex items-start gap-3">
                                    <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
                                    <span x-text="fileError"></span>
                                </div>
                            </div>
                        </template>

                        <template x-if="!fileLoading && !fileError && fileData && fileData.is_text">
                            <div class="space-y-3">
                                <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
                                    <div class="flex flex-wrap gap-2 text-xs">
                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-slate-600">
                                            Підсвітка:
                                            <span class="font-semibold text-slate-900" x-text="activeSyntaxLabel() || 'Text'"></span>
                                        </span>
                                        <span
                                            x-show="hasUnsavedChanges()"
                                            class="rounded-full bg-amber-100 px-3 py-1 text-amber-700"
                                        >
                                            Незбережені зміни
                                        </span>
                                        <span
                                            x-show="editorFallback"
                                            class="rounded-full bg-slate-900 px-3 py-1 text-white"
                                        >
                                            Lite editor
                                        </span>
                                        <span
                                            x-show="saveMessage"
                                            class="rounded-full bg-emerald-100 px-3 py-1 text-emerald-700"
                                            x-text="saveMessage"
                                        ></span>
                                    </div>

                                    <div class="flex flex-wrap gap-2">
                                        <button
                                            type="button"
                                            @click="reloadActiveFile()"
                                            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:bg-slate-50"
                                        >
                                            <i class="fa-solid fa-rotate"></i>
                                            Перечитати
                                        </button>
                                        <button
                                            type="button"
                                            @click="saveCurrentFile()"
                                            :disabled="saveLoading || !hasUnsavedChanges()"
                                            class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60"
                                        >
                                            <i class="fa-regular fa-floppy-disk"></i>
                                            <span x-text="saveLoading ? 'Збереження...' : 'Зберегти'"></span>
                                        </button>
                                    </div>
                                </div>

                                <div
                                    x-ref="editorHost"
                                    :data-editor-fallback="editorFallback ? 'true' : 'false'"
                                    class="relative overflow-hidden rounded-[24px] border border-slate-200 bg-slate-950 p-1 text-slate-100"
                                >
                                    <textarea
                                        x-ref="editorTextarea"
                                        x-model="editorContent"
                                        class="pointer-events-none absolute inset-0 h-0 w-0 opacity-0"
                                    ></textarea>

                                    <div
                                        x-show="editorFallback"
                                        class="fm-v2-lite-shell"
                                    >
                                        <pre
                                            x-ref="liteHighlight"
                                            class="fm-v2-lite-highlight"
                                            x-html="highlightedEditorContent()"
                                        ></pre>
                                        <textarea
                                            x-ref="liteTextarea"
                                            x-model="editorContent"
                                            class="fm-v2-lite-textarea"
                                            spellcheck="false"
                                            wrap="soft"
                                            @input="handleLiteInput"
                                            @scroll="syncLiteEditorScroll()"
                                        ></textarea>
                                    </div>

                                    <div
                                        x-show="!editorFallback && editorAssetsLoading"
                                        class="flex min-h-[420px] items-center justify-center rounded-[20px] border border-slate-800 bg-slate-900 text-sm text-slate-400"
                                    >
                                        Ініціалізація редактора...
                                    </div>
                                </div>
                            </div>
                        </template>

                        <template x-if="!fileLoading && !fileError && fileData && !fileData.is_text">
                            <div class="rounded-[24px] border border-slate-200 bg-slate-50 px-6 py-8">
                                <div class="flex items-start gap-4">
                                    <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-900 text-white">
                                        <i class="fa-regular fa-file"></i>
                                    </span>
                                    <div>
                                        <h3 class="text-lg font-semibold text-slate-900">Бінарний або нетекстовий файл</h3>
                                        <p class="mt-2 text-sm text-slate-600">
                                            Цей тип файлу не редагується в браузері. URL, завантаження і видалення доступні, але редактор не відкривається.
                                        </p>
                                        <div class="mt-3 flex flex-wrap gap-2 text-xs text-slate-500">
                                            <span class="rounded-full bg-white px-3 py-1" x-text="fileData?.mime_type || 'application/octet-stream'"></span>
                                            <span class="rounded-full bg-white px-3 py-1" x-text="formatFileSize(fileData?.size || 0)"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </section>
    </div>
</div>

@php
    $v2Routes = [
        'index' => route('file-manager.v2.index'),
        'info' => route('file-manager.v2.info'),
        'tree' => route('file-manager.v2.tree'),
        'read' => route('file-manager.v2.read'),
        'update' => route('file-manager.v2.update'),
        'download' => route('file-manager.v2.download'),
        'createFile' => route('file-manager.v2.create-file'),
        'createDirectory' => route('file-manager.v2.create-directory'),
        'delete' => route('file-manager.v2.delete'),
    ];

    $codeMirrorSources = [
        route('file-manager.asset', ['path' => 'codemirror/codemirror.min.js']),
        route('file-manager.asset', ['path' => 'codemirror/addon/mode/multiplex.min.js']),
        route('file-manager.asset', ['path' => 'codemirror/mode/clike/clike.min.js']),
        route('file-manager.asset', ['path' => 'codemirror/mode/xml/xml.min.js']),
        route('file-manager.asset', ['path' => 'codemirror/mode/javascript/javascript.min.js']),
        route('file-manager.asset', ['path' => 'codemirror/mode/css/css.min.js']),
        route('file-manager.asset', ['path' => 'codemirror/mode/htmlmixed/htmlmixed.min.js']),
        route('file-manager.asset', ['path' => 'codemirror/mode/php/php.min.js']),
        route('file-manager.asset', ['path' => 'codemirror/mode/markdown/markdown.min.js']),
        route('file-manager.asset', ['path' => 'codemirror/mode/sql/sql.min.js']),
        route('file-manager.asset', ['path' => 'codemirror/mode/shell/shell.min.js']),
        route('file-manager.asset', ['path' => 'codemirror/mode/yaml/yaml.min.js']),
        route('file-manager.asset', ['path' => 'codemirror/mode/properties/properties.min.js']),
        route('file-manager.asset', ['path' => 'codemirror/mode/python/python.min.js']),
    ];

    $v2OpenBaseUrl = rtrim(route('file-manager.v2.index'), '/').'/open';
@endphp

<script>
@include('file-manager::partials.modal-helper-js-code')

const FILE_MANAGER_V2_ROUTES = @js($v2Routes);
const FILE_MANAGER_V2_OPEN_BASE_URL = @js($v2OpenBaseUrl);
const FILE_MANAGER_V2_CODEMIRROR_SOURCES = @js($codeMirrorSources);

const FILE_MANAGER_V2_LANGUAGE_DEFINITIONS = {
    blade: { label: 'Blade / PHP', mode: 'application/x-httpd-php', fallback: 'php' },
    php: { label: 'PHP', mode: 'application/x-httpd-php', fallback: 'php' },
    js: { label: 'JavaScript', mode: 'javascript', fallback: 'js' },
    ts: { label: 'TypeScript', mode: 'javascript', fallback: 'ts' },
    json: { label: 'JSON', mode: 'application/json', fallback: 'js' },
    html: { label: 'HTML', mode: 'htmlmixed', fallback: 'html' },
    css: { label: 'CSS / SCSS', mode: 'css', fallback: 'css' },
    markdown: { label: 'Markdown', mode: 'markdown', fallback: 'md' },
    sql: { label: 'SQL', mode: 'sql', fallback: 'sql' },
    shell: { label: 'Shell', mode: 'shell', fallback: 'shell' },
    xml: { label: 'XML / SVG', mode: 'xml', fallback: 'xml' },
    yaml: { label: 'YAML', mode: 'yaml', fallback: 'yaml' },
    properties: { label: 'ENV / Properties', mode: 'properties', fallback: 'properties' },
    python: { label: 'Python', mode: 'python', fallback: 'python' },
    plain: { label: 'Plain text', mode: 'text/plain', fallback: 'plain' },
};

const FILE_MANAGER_V2_FALLBACK_KEYWORDS = {
    php: ['function', 'class', 'public', 'protected', 'private', 'return', 'if', 'else', 'elseif', 'foreach', 'for', 'while', 'switch', 'case', 'default', 'break', 'continue', 'try', 'catch', 'finally', 'new', 'use', 'namespace', 'extends', 'implements', 'static', 'self', 'parent', 'echo', 'null', 'true', 'false'],
    js: ['function', 'const', 'let', 'var', 'return', 'if', 'else', 'for', 'while', 'switch', 'case', 'default', 'break', 'continue', 'try', 'catch', 'finally', 'class', 'extends', 'new', 'import', 'from', 'export', 'null', 'true', 'false'],
    ts: ['function', 'const', 'let', 'var', 'return', 'if', 'else', 'for', 'while', 'switch', 'case', 'default', 'break', 'continue', 'try', 'catch', 'finally', 'class', 'extends', 'new', 'import', 'from', 'export', 'type', 'interface', 'implements', 'public', 'private', 'protected', 'readonly', 'null', 'true', 'false'],
    css: ['color', 'background', 'display', 'flex', 'grid', 'position', 'absolute', 'relative', 'font', 'padding', 'margin', 'border'],
    sql: ['select', 'insert', 'update', 'delete', 'from', 'where', 'join', 'left', 'right', 'inner', 'outer', 'group', 'by', 'order', 'limit', 'values', 'into', 'create', 'table', 'drop', 'alter'],
    yaml: ['true', 'false', 'null', 'yes', 'no'],
    properties: ['true', 'false', 'null'],
    python: ['def', 'class', 'return', 'if', 'elif', 'else', 'for', 'while', 'break', 'continue', 'try', 'except', 'finally', 'import', 'from', 'as', 'None', 'True', 'False'],
};

function escapeHtmlSafe(value) {
    return (value || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function highlightWithFallback(code, languageKey) {
    const text = typeof code === 'string' ? code : '';
    const patterns = [
        { regex: /\/\*[\s\S]*?\*\//g, className: 'comment' },
        { regex: /(^|[^:])\/\/.*$/gm, className: 'comment' },
        { regex: /#.*$/gm, className: 'comment' },
        { regex: /'(?:\\.|[^'\\])*'|"(?:\\.|[^"\\])*"/g, className: 'string' },
        { regex: /\b\d+(?:\.\d+)?\b/g, className: 'number' },
    ];

    const keywordList = FILE_MANAGER_V2_FALLBACK_KEYWORDS[languageKey] || [];

    if (keywordList.length) {
        patterns.push({
            regex: new RegExp(`\\b(${keywordList.join('|')})\\b`, 'gi'),
            className: 'keyword',
        });
    }

    const matches = [];
    patterns.forEach(pattern => {
        let match;
        while ((match = pattern.regex.exec(text)) !== null) {
            matches.push({
                start: match.index,
                end: match.index + match[0].length,
                className: pattern.className,
                value: match[0],
            });
        }
    });

    matches.sort((a, b) => a.start - b.start || a.end - b.end);

    let cursor = 0;
    let result = '';
    matches.forEach(match => {
        if (match.start < cursor) {
            return;
        }

        result += escapeHtmlSafe(text.slice(cursor, match.start));
        result += `<span class="fm-token fm-${match.className}">${escapeHtmlSafe(match.value)}</span>`;
        cursor = match.end;
    });

    result += escapeHtmlSafe(text.slice(cursor));
    return result || '<span class="fm-token">&nbsp;</span>';
}

function getCaretIndex(node) {
    const selection = window.getSelection();
    if (!selection || selection.rangeCount === 0) {
        return 0;
    }

    const range = selection.getRangeAt(0);
    const preRange = range.cloneRange();
    preRange.selectNodeContents(node);
    preRange.setEnd(range.endContainer, range.endOffset);
    return preRange.toString().length;
}

function setCaretIndex(node, index) {
    const walker = document.createTreeWalker(node, NodeFilter.SHOW_TEXT, null);
    let currentIndex = 0;
    let targetNode = null;
    let targetOffset = 0;

    while (walker.nextNode()) {
        const textNode = walker.currentNode;
        const nextIndex = currentIndex + textNode.textContent.length;

        if (index <= nextIndex) {
            targetNode = textNode;
            targetOffset = index - currentIndex;
            break;
        }

        currentIndex = nextIndex;
    }

    if (!targetNode) {
        targetNode = node;
        targetOffset = node.childNodes.length;
    }

    const range = document.createRange();
    range.setStart(targetNode, targetOffset);
    range.collapse(true);

    const selection = window.getSelection();
    if (selection) {
        selection.removeAllRanges();
        selection.addRange(range);
    }
}

function fileManagerV2(initialPath = '', initialSelection = '', initialTarget = '') {
    return {
        initialPath,
        initialSelection,
        initialTarget,
        currentPath: '',
        activeTargetPath: '',
        items: [],
        treeNodes: [],
        expandedTreePaths: { '': true },
        activeItem: null,
        fileData: null,
        editorContent: '',
        originalContent: '',
        filterTerm: '',
        createMode: '',
        createName: '',
        treeLoading: false,
        fileLoading: false,
        saveLoading: false,
        createLoading: false,
        editorAssetsLoading: false,
        editorAssetsReady: !!window.CodeMirror,
        editorInstance: null,
        editorFallback: false,
        editorMountAttempts: 0,
        fileError: null,
        saveMessage: '',
        notice: {
            type: 'success',
            message: '',
        },
        popstateHandler: null,

        get visibleTreeNodes() {
            const term = this.filterTerm.trim().toLowerCase();

            return this.flattenTreeNodes(this.treeNodes, 0, term);
        },

        async init() {
            const initialTargetPath = this.normalizePath(this.initialTarget || this.initialSelection || this.initialPath || '');
            const hasInitialFileTarget = initialTargetPath !== '' && this.normalizePath(this.initialSelection || '') === initialTargetPath;
            this.popstateHandler = async (event) => {
                const targetPath = typeof event.state?.targetPath === 'string'
                    ? event.state.targetPath
                    : this.normalizePath(this.initialTarget || '');
                await this.openTarget(targetPath, { pushState: false, replaceState: false, warnOnUnsaved: true });
            };

            window.addEventListener('popstate', this.popstateHandler);
            await new Promise(resolve => this.$nextTick(resolve));
            await this.openTarget(initialTargetPath, {
                pushState: false,
                replaceState: true,
                warnOnUnsaved: false,
                preserveTreeOnFileSelection: hasInitialFileTarget,
            });

            if (hasInitialFileTarget) {
                await this.ensureInitialFileTarget(initialTargetPath);
            }
        },

        async openTarget(path = '', options = {}) {
            const normalizedPath = this.normalizePath(path);

            if (options.warnOnUnsaved !== false && !(await this.confirmDiscardChanges())) {
                return;
            }

            this.saveMessage = '';
            this.fileError = null;

            if (!normalizedPath) {
                await this.ensureTreePath('');
                await this.loadDirectory('');
                this.activateDirectory('');
                this.updateHistory('', options);
                return;
            }

            const existingNode = this.findTreeNode(normalizedPath) || this.items.find(item => item.path === normalizedPath);

            if (existingNode?.type === 'directory') {
                await this.ensureTreePath(normalizedPath);
                await this.loadDirectory(normalizedPath);
                this.activateDirectory(normalizedPath, existingNode);
                this.updateHistory(normalizedPath, options);
                return;
            }

            if (options.preserveTreeOnFileSelection && await this.openFileFromLoadedTree(normalizedPath, options)) {
                return;
            }

            const parentPath = this.parentPath(normalizedPath);
            await this.ensureTreePath(parentPath);
            await this.loadDirectory(parentPath);

            const loadedNode = this.items.find(item => item.path === normalizedPath) || this.findTreeNode(normalizedPath);

            if (loadedNode?.type === 'directory') {
                await this.ensureTreePath(normalizedPath);
                await this.loadDirectory(normalizedPath);
                this.activateDirectory(normalizedPath, loadedNode);
                this.updateHistory(normalizedPath, options);
                return;
            }

            if (await this.openFileFromLoadedTree(normalizedPath, options)) {
                return;
            }

            const info = await this.fetchInfo(normalizedPath);

            if (!info) {
                return;
            }

            if (info.type === 'directory') {
                await this.ensureTreePath(normalizedPath);
                await this.loadDirectory(normalizedPath);
                this.activateDirectory(normalizedPath, info);
                this.updateHistory(normalizedPath, options);
                return;
            }

            this.activeItem = info;
            this.activeTargetPath = normalizedPath;
            await this.loadFile(normalizedPath);
            this.updateHistory(normalizedPath, options);
        },

        async loadDirectory(path = '', options = {}) {
            const normalizedPath = this.normalizePath(path);
            const setCurrent = options.setCurrent !== false;
            const silent = options.silent === true;

            if (setCurrent) {
                this.treeLoading = true;
            }

            try {
                const data = await this.fetchDirectoryData(normalizedPath, { silent });

                if (!data) {
                    return false;
                }

                this.syncTreeBranch(normalizedPath, data.items || []);
                this.expandTreePath(normalizedPath);

                if (setCurrent) {
                    this.items = data.items || [];
                    this.currentPath = data.path || '';

                    if (this.activeItem?.type === 'file') {
                        this.activeItem = this.items.find(item => item.path === this.activeTargetPath) || this.activeItem;
                    }
                }

                return true;
            } catch (error) {
                console.error(error);
                if (!silent) {
                    this.setNotice('Помилка з’єднання при завантаженні каталогу', 'error');
                }
                return false;
            } finally {
                if (setCurrent) {
                    this.treeLoading = false;
                }
            }
        },

        async fetchDirectoryData(path = '', options = {}) {
            const normalizedPath = this.normalizePath(path);
            const response = await fetch(`${FILE_MANAGER_V2_ROUTES.tree}?path=${encodeURIComponent(normalizedPath)}`, {
                headers: { 'Accept': 'application/json' },
            });
            const data = await response.json();

            if (!data.success) {
                if (!options.silent) {
                    this.setNotice(data.error || 'Не вдалося відкрити каталог', 'error');
                }

                return null;
            }

            return data;
        },

        async fetchInfo(path) {
            try {
                const response = await fetch(`${FILE_MANAGER_V2_ROUTES.info}?path=${encodeURIComponent(path)}`, {
                    headers: { 'Accept': 'application/json' },
                });
                const data = await response.json();

                if (!data.success) {
                    this.setNotice(data.error || 'Елемент не знайдено', 'error');
                    return null;
                }

                return data.info;
            } catch (error) {
                console.error(error);
                this.setNotice('Помилка з’єднання при перевірці шляху', 'error');
                return null;
            }
        },

        createTreeNodes(items = []) {
            return items.map(item => {
                const existingNode = this.findTreeNode(item.path);

                return {
                    ...item,
                    children: item.type === 'directory' ? (existingNode?.children || []) : [],
                    childrenLoaded: item.type === 'directory' ? (existingNode?.childrenLoaded || false) : true,
                };
            });
        },

        syncTreeBranch(path = '', items = []) {
            const normalizedPath = this.normalizePath(path);
            const preparedNodes = this.createTreeNodes(items);

            if (!normalizedPath) {
                this.treeNodes = preparedNodes;
                return;
            }

            const targetNode = this.findTreeNode(normalizedPath);

            if (!targetNode) {
                return;
            }

            targetNode.children = preparedNodes;
            targetNode.childrenLoaded = true;
        },

        findTreeNode(path = '', nodes = this.treeNodes) {
            const normalizedPath = this.normalizePath(path);

            for (const node of nodes) {
                if (node.path === normalizedPath) {
                    return node;
                }

                if (node.type === 'directory' && Array.isArray(node.children) && node.children.length > 0) {
                    const match = this.findTreeNode(normalizedPath, node.children);

                    if (match) {
                        return match;
                    }
                }
            }

            return null;
        },

        expandTreePath(path = '') {
            const normalizedPath = this.normalizePath(path);
            this.expandedTreePaths[''] = true;

            if (!normalizedPath) {
                return;
            }

            let current = '';
            normalizedPath.split('/').forEach(part => {
                current = current ? `${current}/${part}` : part;
                this.expandedTreePaths[current] = true;
            });
        },

        isTreeExpanded(path = '') {
            const normalizedPath = this.normalizePath(path);

            if (!normalizedPath) {
                return true;
            }

            return !!this.expandedTreePaths[normalizedPath];
        },

        async toggleTreeNode(node) {
            if (!node || node.type !== 'directory') {
                return;
            }

            const normalizedPath = this.normalizePath(node.path);
            const currentlyExpanded = this.isTreeExpanded(normalizedPath);

            if (currentlyExpanded) {
                this.expandedTreePaths[normalizedPath] = false;
                return;
            }

            this.expandedTreePaths[normalizedPath] = true;

            if (!node.childrenLoaded) {
                await this.loadDirectory(normalizedPath, { setCurrent: false });
            }
        },

        async ensureTreePath(path = '') {
            const normalizedPath = this.normalizePath(path);
            this.expandedTreePaths[''] = true;

            await this.loadDirectory('', { setCurrent: false, silent: true });

            if (!normalizedPath) {
                return;
            }

            const parts = normalizedPath.split('/');
            let current = '';

            for (const part of parts) {
                current = current ? `${current}/${part}` : part;
                this.expandedTreePaths[current] = true;
                await this.loadDirectory(current, { setCurrent: false, silent: true });
            }
        },

        flattenTreeNodes(nodes = [], depth = 0, filter = '') {
            const result = [];

            nodes.forEach(node => {
                const children = Array.isArray(node.children) ? node.children : [];
                const matchesFilter = !filter || node.name.toLowerCase().includes(filter);
                const shouldTraverseChildren = node.type === 'directory' && (this.isTreeExpanded(node.path) || filter !== '');
                const flattenedChildren = shouldTraverseChildren
                    ? this.flattenTreeNodes(children, depth + 1, filter)
                    : [];
                const shouldInclude = !filter || matchesFilter || flattenedChildren.length > 0;

                if (!shouldInclude) {
                    return;
                }

                result.push({
                    ...node,
                    depth,
                });

                result.push(...flattenedChildren);
            });

            return result;
        },

        isFileTargetActive(path = '') {
            const normalizedPath = this.normalizePath(path);

            return normalizedPath !== ''
                && this.normalizePath(this.activeTargetPath) === normalizedPath
                && this.activeItem?.type === 'file'
                && (
                    this.fileLoading
                    || this.fileError !== null
                    || this.normalizePath(this.fileData?.path || '') === normalizedPath
                );
        },

        async ensureInitialFileTarget(path = '') {
            const normalizedPath = this.normalizePath(path);

            if (!normalizedPath || this.isFileTargetActive(normalizedPath)) {
                return;
            }

            for (const delay of [80, 220, 500]) {
                await new Promise(resolve => setTimeout(resolve, delay));

                if (this.isFileTargetActive(normalizedPath)) {
                    return;
                }

                await this.openTarget(normalizedPath, {
                    pushState: false,
                    replaceState: true,
                    warnOnUnsaved: false,
                    preserveTreeOnFileSelection: true,
                });

                if (this.isFileTargetActive(normalizedPath)) {
                    return;
                }
            }
        },

        async openFileFromLoadedTree(path, options = {}) {
            const normalizedPath = this.normalizePath(path);
            const selectedNode = this.findTreeNode(normalizedPath);

            if (!selectedNode || selectedNode.type !== 'file') {
                return false;
            }

            const parentPath = this.parentPath(normalizedPath);
            const parentNode = parentPath ? this.findTreeNode(parentPath) : null;

            this.expandTreePath(parentPath);
            this.currentPath = parentPath;
            this.items = parentPath
                ? (Array.isArray(parentNode?.children) ? parentNode.children : [])
                : this.treeNodes;
            this.activeItem = selectedNode;
            this.activeTargetPath = normalizedPath;

            await this.loadFile(normalizedPath);
            this.updateHistory(normalizedPath, options);

            return true;
        },

        activateDirectory(path, info = null) {
            const normalizedPath = this.normalizePath(path);
            this.currentPath = normalizedPath;
            this.activeTargetPath = normalizedPath;
            this.activeItem = info || this.buildDirectoryDescriptor(normalizedPath);
            this.fileData = null;
            this.fileError = null;
            this.editorContent = '';
            this.originalContent = '';
            this.editorFallback = false;
            this.editorAssetsLoading = false;
            this.editorMountAttempts = 0;
            this.destroyEditor();
        },

        async loadFile(path) {
            this.fileLoading = true;
            this.fileError = null;
            this.fileData = null;
            this.saveMessage = '';
            this.editorAssetsLoading = false;
            this.editorFallback = false;
            this.editorMountAttempts = 0;
            this.destroyEditor();

            try {
                const response = await fetch(`${FILE_MANAGER_V2_ROUTES.read}?path=${encodeURIComponent(path)}`, {
                    headers: { 'Accept': 'application/json' },
                });
                const data = await response.json();

                if (!data.success) {
                    this.fileError = data.error || 'Не вдалося завантажити файл';
                    return;
                }

                this.fileData = data.file;
                this.activeItem = {
                    ...(this.activeItem || {}),
                    ...data.file,
                    type: 'file',
                };

                if (!data.file.is_text) {
                    this.editorContent = '';
                    this.originalContent = '';
                    return;
                }

                this.editorContent = data.file.content || '';
                this.originalContent = this.editorContent;

                try {
                    this.editorAssetsLoading = true;
                    await this.ensureEditorAssets();
                    this.$nextTick(() => this.mountEditor());
                } catch (assetError) {
                    console.error(assetError);
                    this.editorFallback = true;
                    this.$nextTick(() => this.mountLiteEditor());
                }
            } catch (error) {
                console.error(error);
                this.fileError = 'Помилка з’єднання при читанні файлу';
            } finally {
                this.fileLoading = false;
                if (!this.fileData?.is_text) {
                    this.editorAssetsLoading = false;
                }
            }
        },

        async saveCurrentFile() {
            if (!this.fileData?.is_text || !this.activeTargetPath) {
                return;
            }

            this.saveLoading = true;
            this.fileError = null;
            this.saveMessage = '';

            const payload = {
                path: this.activeTargetPath,
                content: this.currentEditorValue(),
            };

            try {
                const response = await fetch(FILE_MANAGER_V2_ROUTES.update, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.getCsrfToken(),
                    },
                    body: JSON.stringify(payload),
                });
                const data = await response.json();

                if (!data.success) {
                    this.fileError = data.error || 'Помилка збереження';
                    return;
                }

                this.originalContent = payload.content;
                this.saveMessage = 'Файл збережено';
                this.setNotice('Файл успішно збережено', 'success');
                await this.loadDirectory(this.currentPath);
            } catch (error) {
                console.error(error);
                this.fileError = 'Помилка з’єднання при збереженні файлу';
            } finally {
                this.saveLoading = false;
            }
        },

        async reloadActiveFile() {
            if (!this.activeTargetPath) {
                return;
            }

            if (!(await this.confirmDiscardChanges())) {
                return;
            }

            await this.openTarget(this.activeTargetPath, { pushState: false, replaceState: true, warnOnUnsaved: false });
        },

        async refreshCurrentDirectory() {
            await this.loadDirectory(this.currentPath);

            if (this.activeItem?.type === 'file' && this.activeTargetPath) {
                this.activeItem = this.items.find(item => item.path === this.activeTargetPath) || this.activeItem;
            }
        },

        async openDirectory(path) {
            await this.openTarget(path, { pushState: true, replaceState: false, warnOnUnsaved: true });
        },

        async openFile(path) {
            await this.openTarget(path, {
                pushState: true,
                replaceState: false,
                warnOnUnsaved: true,
                preserveTreeOnFileSelection: true,
            });
        },

        async goToRoot() {
            await this.openTarget('', { pushState: true, replaceState: false, warnOnUnsaved: true });
        },

        toggleCreate(mode) {
            if (this.createMode === mode) {
                this.cancelCreate();
                return;
            }

            this.createMode = mode;
            this.createName = '';
        },

        cancelCreate() {
            this.createMode = '';
            this.createName = '';
            this.createLoading = false;
        },

        async submitCreate() {
            const trimmedName = this.createName.trim();
            const targetPath = this.buildPath(this.currentPath, trimmedName);

            if (!this.createMode || !targetPath) {
                return;
            }

            this.createLoading = true;

            try {
                const route = this.createMode === 'file'
                    ? FILE_MANAGER_V2_ROUTES.createFile
                    : FILE_MANAGER_V2_ROUTES.createDirectory;

                const response = await fetch(route, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.getCsrfToken(),
                    },
                    body: JSON.stringify({
                        path: targetPath,
                        content: '',
                    }),
                });
                const data = await response.json();

                if (!data.success) {
                    this.setNotice(data.error || 'Не вдалося створити елемент', 'error');
                    return;
                }

                this.cancelCreate();
                this.setNotice(data.message || 'Елемент створено', 'success');
                await this.openTarget(data.path || targetPath, { pushState: true, replaceState: false, warnOnUnsaved: false });
            } catch (error) {
                console.error(error);
                this.setNotice('Помилка з’єднання при створенні елемента', 'error');
            } finally {
                this.createLoading = false;
            }
        },

        async confirmDelete(item) {
            if (!item?.path) {
                return;
            }

            const itemTypeLabel = item.type === 'directory'
                ? 'папку разом з усім вмістом'
                : 'файл';
            const confirmed = await this.confirmAction({
                title: 'Підтвердження видалення',
                message: `Видалити ${itemTypeLabel} "${item.path}"?`,
                confirmLabel: 'Видалити',
                cancelLabel: 'Скасувати',
                variant: 'danger',
            });

            if (!confirmed) {
                return;
            }

            try {
                const response = await fetch(FILE_MANAGER_V2_ROUTES.delete, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.getCsrfToken(),
                    },
                    body: JSON.stringify({ path: item.path }),
                });
                const data = await response.json();

                if (!data.success) {
                    this.setNotice(data.error || 'Не вдалося видалити елемент', 'error');
                    return;
                }

                this.setNotice(data.message || 'Елемент видалено', 'success');

                const deletedPath = this.normalizePath(item.path);
                const fallbackPath = this.parentPath(deletedPath);
                const affectsCurrentTarget = this.activeTargetPath === deletedPath
                    || this.currentPath === deletedPath
                    || (this.activeTargetPath && this.activeTargetPath.startsWith(`${deletedPath}/`));

                if (affectsCurrentTarget) {
                    await this.openTarget(fallbackPath, { pushState: true, replaceState: false, warnOnUnsaved: false });
                    return;
                }

                await this.loadDirectory(this.parentPath(deletedPath), { setCurrent: false, silent: true });
                await this.loadDirectory(this.currentPath);
            } catch (error) {
                console.error(error);
                this.setNotice('Помилка з’єднання при видаленні елемента', 'error');
            }
        },

        downloadActive() {
            if (!this.activeTargetPath || this.activeItem?.type !== 'file') {
                return;
            }

            window.location.href = `${FILE_MANAGER_V2_ROUTES.download}?path=${encodeURIComponent(this.activeTargetPath)}`;
        },

        async copyCurrentUrl() {
            const sourcePath = this.activeTargetPath || this.currentPath;
            await this.copyText(this.buildTargetUrl(sourcePath), 'URL скопійовано');
        },

        async copyTargetUrl(path) {
            await this.copyText(this.buildTargetUrl(path), 'URL скопійовано');
        },

        async copyText(value, successMessage) {
            try {
                if (navigator.clipboard?.writeText) {
                    await navigator.clipboard.writeText(value);
                } else {
                    const textarea = document.createElement('textarea');
                    textarea.value = value;
                    textarea.setAttribute('readonly', 'readonly');
                    textarea.style.position = 'absolute';
                    textarea.style.left = '-9999px';
                    document.body.appendChild(textarea);
                    textarea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textarea);
                }

                this.setNotice(successMessage, 'success');
            } catch (error) {
                console.error(error);
                this.setNotice('Не вдалося скопіювати URL', 'error');
            }
        },

        updateHistory(path, options = {}) {
            const normalizedPath = this.normalizePath(path);
            const url = this.buildTargetUrl(normalizedPath);
            const state = { targetPath: normalizedPath };

            if (options.pushState) {
                window.history.pushState(state, '', url);
                return;
            }

            if (options.replaceState || !window.history.state) {
                window.history.replaceState(state, '', url);
            }
        },

        buildTargetUrl(path = '') {
            const normalizedPath = this.normalizePath(path);

            if (!normalizedPath) {
                return FILE_MANAGER_V2_ROUTES.index;
            }

            const encodedPath = normalizedPath
                .split('/')
                .map(segment => encodeURIComponent(segment))
                .join('/');

            return `${FILE_MANAGER_V2_OPEN_BASE_URL}/${encodedPath}`;
        },

        buildPath(basePath = '', childPath = '') {
            const normalizedChild = this.normalizePath(childPath);
            const normalizedBase = this.normalizePath(basePath);

            if (!normalizedChild) {
                return normalizedBase;
            }

            return normalizedBase
                ? `${normalizedBase}/${normalizedChild}`
                : normalizedChild;
        },

        parentPath(path = '') {
            const normalizedPath = this.normalizePath(path);

            if (!normalizedPath || !normalizedPath.includes('/')) {
                return '';
            }

            return normalizedPath.split('/').slice(0, -1).join('/');
        },

        basename(path = '') {
            const normalizedPath = this.normalizePath(path);

            if (!normalizedPath) {
                return '/';
            }

            const parts = normalizedPath.split('/');
            return parts[parts.length - 1];
        },

        normalizePath(path = '') {
            return String(path || '')
                .replace(/\\/g, '/')
                .replace(/\/+/g, '/')
                .replace(/^\/+|\/+$/g, '')
                .trim();
        },

        breadcrumbItems() {
            if (!this.currentPath) {
                return [{ label: 'root', path: '' }];
            }

            const parts = this.currentPath.split('/');
            const crumbs = [{ label: 'root', path: '' }];
            let current = '';

            parts.forEach(part => {
                current = current ? `${current}/${part}` : part;
                crumbs.push({ label: part, path: current });
            });

            return crumbs;
        },

        buildDirectoryDescriptor(path = '') {
            const normalizedPath = this.normalizePath(path);

            return {
                name: normalizedPath ? this.basename(normalizedPath) : '/',
                path: normalizedPath,
                type: 'directory',
                modified: null,
                readable: true,
                writable: true,
            };
        },

        detectLanguageKey(path = '') {
            const filePath = this.normalizePath(path).toLowerCase();
            const filename = this.basename(filePath).toLowerCase();

            if (filename === '.env' || filename.endsWith('.env') || filePath.endsWith('.ini') || filePath.endsWith('.conf') || filePath.endsWith('.properties')) {
                return 'properties';
            }

            if (filePath.endsWith('.blade.php')) {
                return 'blade';
            }

            if (filePath.endsWith('.yaml') || filePath.endsWith('.yml')) {
                return 'yaml';
            }

            if (filePath.endsWith('.py')) {
                return 'python';
            }

            if (filePath.endsWith('.md') || filePath.endsWith('.markdown')) {
                return 'markdown';
            }

            if (filePath.endsWith('.sql')) {
                return 'sql';
            }

            if (filePath.endsWith('.sh') || filePath.endsWith('.bash') || filePath.endsWith('.zsh') || filePath.endsWith('.bat') || filePath.endsWith('.cmd')) {
                return 'shell';
            }

            if (filePath.endsWith('.xml') || filePath.endsWith('.svg')) {
                return 'xml';
            }

            if (filePath.endsWith('.css') || filePath.endsWith('.scss') || filePath.endsWith('.sass')) {
                return 'css';
            }

            if (filePath.endsWith('.html') || filePath.endsWith('.htm') || filePath.endsWith('.vue')) {
                return 'html';
            }

            if (filePath.endsWith('.json')) {
                return 'json';
            }

            if (filePath.endsWith('.ts') || filePath.endsWith('.tsx')) {
                return 'ts';
            }

            if (filePath.endsWith('.js') || filePath.endsWith('.mjs') || filePath.endsWith('.cjs')) {
                return 'js';
            }

            if (filePath.endsWith('.php')) {
                return 'php';
            }

            return 'plain';
        },

        getLanguageDefinition(path = '') {
            const key = this.detectLanguageKey(path);
            return FILE_MANAGER_V2_LANGUAGE_DEFINITIONS[key] || FILE_MANAGER_V2_LANGUAGE_DEFINITIONS.plain;
        },

        activeSyntaxLabel() {
            if (!this.activeTargetPath) {
                return '';
            }

            return this.getLanguageDefinition(this.activeTargetPath).label;
        },

        currentEditorValue() {
            if (this.editorInstance) {
                return this.editorInstance.getValue();
            }

            return this.editorContent || '';
        },

        highlightedEditorContent() {
            const languageKey = this.getLanguageDefinition(this.activeTargetPath).fallback;
            return highlightWithFallback(this.editorContent || '', languageKey);
        },

        hasUnsavedChanges() {
            if (!this.fileData?.is_text) {
                return false;
            }

            return this.currentEditorValue() !== this.originalContent;
        },

        async confirmAction(options = {}) {
            if (window.FileManagerModal?.confirm) {
                return window.FileManagerModal.confirm(options);
            }

            return false;
        },

        async confirmDiscardChanges() {
            if (!this.hasUnsavedChanges()) {
                return true;
            }

            return this.confirmAction({
                title: 'Незбережені зміни',
                message: 'Є незбережені зміни. Втратити їх?',
                confirmLabel: 'Втратити зміни',
                cancelLabel: 'Скасувати',
                variant: 'danger',
            });
        },

        async ensureEditorAssets() {
            if (this.editorAssetsReady && window.CodeMirror) {
                return;
            }

            let attempts = 0;

            while (!window.CodeMirror && attempts < 60) {
                await new Promise(resolve => setTimeout(resolve, 100));
                attempts++;
            }

            if (!window.CodeMirror) {
                throw new Error('CodeMirror недоступний після завантаження');
            }

            this.editorAssetsReady = true;
        },

        mountEditor() {
            if (!this.fileData?.is_text || this.editorFallback) {
                this.editorAssetsLoading = false;
                return;
            }

            if (this.editorMountAttempts > 40) {
                this.editorFallback = true;
                this.editorAssetsLoading = false;
                this.$nextTick(() => this.mountLiteEditor());
                return;
            }

            if (!window.CodeMirror || !this.$refs.editorTextarea) {
                this.editorMountAttempts++;
                setTimeout(() => this.mountEditor(), 80);
                return;
            }

            try {
                this.destroyEditor();
                this.editorInstance = CodeMirror.fromTextArea(this.$refs.editorTextarea, {
                    lineNumbers: true,
                    tabSize: 4,
                    indentUnit: 4,
                    lineWrapping: true,
                    mode: this.getLanguageDefinition(this.activeTargetPath).mode,
                    theme: 'fm-v2',
                });
                const editorHeight = Math.min(Math.max(window.innerHeight * 0.62, 420), 900);
                this.editorInstance.setSize('100%', `${editorHeight}px`);
                this.editorInstance.setValue(this.editorContent);
                this.editorInstance.scrollTo(0, 0);
                this.editorInstance.setCursor({ line: 0, ch: 0 });
                this.editorInstance.focus();
                requestAnimationFrame(() => {
                    if (!this.editorInstance) {
                        return;
                    }

                    this.editorInstance.scrollTo(0, 0);
                    this.editorInstance.setCursor({ line: 0, ch: 0 });
                });
                this.editorMountAttempts = 0;
                this.editorAssetsLoading = false;
            } catch (error) {
                console.error(error);
                this.editorFallback = true;
                this.editorAssetsLoading = false;
                this.$nextTick(() => this.mountLiteEditor());
            }
        },

        destroyEditor() {
            if (this.editorInstance) {
                this.editorInstance.toTextArea();
                this.editorInstance = null;
            }

            this.removeStrayCodeMirror();
        },

        mountLiteEditor() {
            if (!this.$refs.liteTextarea) {
                return;
            }

            this.destroyEditor();
            this.syncLiteEditorScroll();
            this.resetLiteEditorViewport();
        },

        refreshLiteHighlight() {
            if (!this.editorFallback || !this.$refs.liteTextarea) {
                return;
            }

            this.$nextTick(() => this.syncLiteEditorScroll());
        },

        resetLiteEditorViewport() {
            if (!this.$refs.liteTextarea) {
                return;
            }

            const resetScroll = () => {
                if (!this.$refs.liteTextarea) {
                    return;
                }

                this.$refs.liteTextarea.scrollTop = 0;
                this.$refs.liteTextarea.scrollLeft = 0;
                this.syncLiteEditorScroll();
            };

            resetScroll();

            [0, 40, 120, 240].forEach(delay => {
                setTimeout(() => {
                    resetScroll();
                }, delay);
            });
        },

        syncLiteEditorScroll() {
            if (!this.$refs.liteTextarea || !this.$refs.liteHighlight) {
                return;
            }

            this.$refs.liteHighlight.scrollTop = this.$refs.liteTextarea.scrollTop;
            this.$refs.liteHighlight.scrollLeft = this.$refs.liteTextarea.scrollLeft;
        },

        removeStrayCodeMirror() {
            if (!this.$refs.editorHost) {
                return;
            }

            this.$refs.editorHost
                .querySelectorAll('.CodeMirror')
                .forEach(node => node.remove());
        },

        handleLiteInput(event) {
            this.editorContent = event.target.value;
            this.$nextTick(() => this.syncLiteEditorScroll());
        },

        getFileIcon(item) {
            const extension = (item.extension || '').toLowerCase();
            const iconMap = {
                php: 'fa-regular fa-file-code text-purple-600',
                js: 'fa-regular fa-file-code text-yellow-600',
                ts: 'fa-regular fa-file-code text-blue-600',
                json: 'fa-regular fa-file-code text-emerald-600',
                html: 'fa-regular fa-file-code text-orange-600',
                css: 'fa-regular fa-file-code text-cyan-600',
                md: 'fa-regular fa-file-lines text-slate-600',
                sql: 'fa-regular fa-file-code text-indigo-600',
                yml: 'fa-regular fa-file-code text-rose-600',
                yaml: 'fa-regular fa-file-code text-rose-600',
                png: 'fa-regular fa-file-image text-pink-600',
                jpg: 'fa-regular fa-file-image text-pink-600',
                jpeg: 'fa-regular fa-file-image text-pink-600',
                gif: 'fa-regular fa-file-image text-pink-600',
                svg: 'fa-regular fa-file-image text-pink-600',
                zip: 'fa-regular fa-file-zipper text-slate-600',
            };

            return iconMap[extension] || 'fa-regular fa-file text-slate-500';
        },

        formatFileSize(bytes) {
            if (!bytes) {
                return '0 B';
            }

            const units = ['B', 'KB', 'MB', 'GB', 'TB'];
            let size = bytes;
            let unitIndex = 0;

            while (size >= 1024 && unitIndex < units.length - 1) {
                size /= 1024;
                unitIndex++;
            }

            return `${size.toFixed(size >= 10 || unitIndex === 0 ? 0 : 2)} ${units[unitIndex]}`;
        },

        formatDate(timestamp) {
            if (!timestamp) {
                return '—';
            }

            return new Date(timestamp * 1000).toLocaleString('uk-UA');
        },

        getCsrfToken() {
            return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        },

        setNotice(message, type = 'success') {
            this.notice = {
                type,
                message,
            };
        },
    };
}
</script>
@endsection

@push('head-scripts')
<link rel="stylesheet" href="{{ route('file-manager.asset', ['path' => 'codemirror/codemirror.min.css']) }}" />
<script defer src="{{ route('file-manager.asset', ['path' => 'codemirror/codemirror.min.js']) }}"></script>
<script defer src="{{ route('file-manager.asset', ['path' => 'codemirror/addon/mode/multiplex.min.js']) }}"></script>
<script defer src="{{ route('file-manager.asset', ['path' => 'codemirror/mode/clike/clike.min.js']) }}"></script>
<script defer src="{{ route('file-manager.asset', ['path' => 'codemirror/mode/xml/xml.min.js']) }}"></script>
<script defer src="{{ route('file-manager.asset', ['path' => 'codemirror/mode/javascript/javascript.min.js']) }}"></script>
<script defer src="{{ route('file-manager.asset', ['path' => 'codemirror/mode/css/css.min.js']) }}"></script>
<script defer src="{{ route('file-manager.asset', ['path' => 'codemirror/mode/htmlmixed/htmlmixed.min.js']) }}"></script>
<script defer src="{{ route('file-manager.asset', ['path' => 'codemirror/mode/php/php.min.js']) }}"></script>
<script defer src="{{ route('file-manager.asset', ['path' => 'codemirror/mode/markdown/markdown.min.js']) }}"></script>
<script defer src="{{ route('file-manager.asset', ['path' => 'codemirror/mode/sql/sql.min.js']) }}"></script>
<script defer src="{{ route('file-manager.asset', ['path' => 'codemirror/mode/shell/shell.min.js']) }}"></script>
<script defer src="{{ route('file-manager.asset', ['path' => 'codemirror/mode/yaml/yaml.min.js']) }}"></script>
<script defer src="{{ route('file-manager.asset', ['path' => 'codemirror/mode/properties/properties.min.js']) }}"></script>
<script defer src="{{ route('file-manager.asset', ['path' => 'codemirror/mode/python/python.min.js']) }}"></script>
<style>
    .shadow-soft {
        box-shadow: 0 26px 60px -32px rgba(15, 23, 42, 0.28);
    }

    .CodeMirror {
        min-height: 420px;
        height: auto;
        border-radius: 20px;
        padding: 0;
        background: #020617;
        color: #e2e8f0;
        font-size: 14px;
        line-height: 1.7;
    }

    .CodeMirror-scroll,
    .CodeMirror-sizer,
    .CodeMirror-lines {
        min-height: 418px;
    }

    .CodeMirror-lines {
        padding: 0.25rem 0 0.75rem;
    }

    .CodeMirror pre {
        padding-top: 0;
        padding-bottom: 0;
    }

    .CodeMirror-gutters {
        background: #020617;
        border-right: 1px solid rgba(148, 163, 184, 0.18);
    }

    .CodeMirror-linenumber {
        color: #64748b;
    }

    [data-editor-fallback="true"] .CodeMirror {
        display: none !important;
    }

    .cm-s-fm-v2.CodeMirror {
        background: #020617;
        color: #e2e8f0;
    }

    .cm-s-fm-v2 .CodeMirror-cursor {
        border-left: 1px solid #f8fafc;
    }

    .cm-s-fm-v2 .cm-comment {
        color: #94a3b8;
        font-style: italic;
    }

    .cm-s-fm-v2 .cm-keyword,
    .cm-s-fm-v2 .cm-tag,
    .cm-s-fm-v2 .cm-operator {
        color: #7dd3fc;
    }

    .cm-s-fm-v2 .cm-def,
    .cm-s-fm-v2 .cm-property {
        color: #f9a8d4;
    }

    .cm-s-fm-v2 .cm-variable,
    .cm-s-fm-v2 .cm-variable-2,
    .cm-s-fm-v2 .cm-variable-3 {
        color: #e2e8f0;
    }

    .cm-s-fm-v2 .cm-atom,
    .cm-s-fm-v2 .cm-number {
        color: #fbbf24;
    }

    .cm-s-fm-v2 .cm-string,
    .cm-s-fm-v2 .cm-string-2,
    .cm-s-fm-v2 .cm-attribute {
        color: #86efac;
    }

    .cm-s-fm-v2 .cm-meta,
    .cm-s-fm-v2 .cm-qualifier,
    .cm-s-fm-v2 .cm-builtin,
    .cm-s-fm-v2 .cm-type {
        color: #c4b5fd;
    }

    .cm-s-fm-v2 .cm-link {
        color: #38bdf8;
        text-decoration: underline;
    }

    .fm-v2-lite-shell {
        position: relative;
        height: clamp(420px, 62vh, 900px);
        overflow: hidden;
        border-radius: 20px;
        background: #020617;
    }

    .fm-v2-lite-highlight,
    .fm-v2-lite-textarea {
        position: absolute;
        inset: 0;
        margin: 0;
        padding: 0.5rem 1rem 1rem;
        overflow: auto;
        white-space: pre-wrap;
        overflow-wrap: break-word;
        word-break: normal;
        tab-size: 4;
        font-family: Consolas, "Fira Code", monospace;
        font-size: 14px;
        line-height: 1.7;
    }

    .fm-v2-lite-highlight {
        pointer-events: none;
        color: #e2e8f0;
    }

    .fm-v2-lite-textarea {
        z-index: 1;
        width: 100%;
        height: 100%;
        resize: none;
        border: 0;
        outline: none;
        background: transparent;
        color: transparent;
        caret-color: #f8fafc;
        -webkit-text-fill-color: transparent;
    }

    .fm-v2-lite-textarea::selection {
        background: rgba(56, 189, 248, 0.24);
    }

    .fm-token.fm-keyword {
        color: #7dd3fc;
        font-weight: 600;
    }

    .fm-token.fm-string {
        color: #86efac;
    }

    .fm-token.fm-comment {
        color: #94a3b8;
        font-style: italic;
    }

    .fm-token.fm-number {
        color: #fbbf24;
    }
</style>
@endpush
