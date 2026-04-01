@extends('layouts.app')

@section('title', 'Файловий менеджер')

@php
    $managerIndexUrl = route('file-manager.index');
    $managerOpenBaseUrl = rtrim($managerIndexUrl, '/').'/open';
    $embedPageUrl = route('file-manager.embed');
    $embedBootstrapUrl = route('file-manager.embed.bootstrap');
@endphp

@section('content')
<div
    class="container mx-auto px-4"
    x-data="fileManager(@js($initialPath ?? ''), @js($initialSelection ?? ''), @js($initialTarget ?? ''), @js($initialMissingTarget ?? ''))"
    x-init="init()"
    @keydown.escape.window="fileModalOpen && closeFileModal()"
>
    <div class="mb-6">
        <div class="mb-2 flex items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Файловий менеджер</h1>
                <p class="mt-2 text-gray-600">Файли відкриваються через AJAX у popup-модалі з новим embeddable-редактором.</p>
                <p class="mt-1 text-sm text-gray-500">Базова директорія: <code class="rounded bg-gray-100 px-2 py-1">{{ $basePath }}</code></p>
            </div>
            <div class="inline-flex rounded-lg border border-gray-300 bg-white p-1 shadow-sm">
                <button class="cursor-default rounded-md bg-blue-600 px-4 py-2 font-medium text-white">
                    <i class="fas fa-list mr-2"></i>Файловий менеджер
                </button>
                <a href="{{ route('file-manager.ide') }}" class="rounded-md px-4 py-2 font-medium text-gray-700 transition hover:bg-gray-100">
                    <i class="fas fa-code mr-2"></i>IDE режим
                </a>
            </div>
        </div>
    </div>

    <div class="mb-4 rounded-lg bg-white p-4 shadow">
        <div class="flex flex-wrap items-center gap-2 text-sm">
            <button type="button" @click="navigateToRoot()" class="rounded bg-blue-500 px-3 py-1 text-white transition hover:bg-blue-600" title="Кореневий каталог">
                <i class="fas fa-home"></i>
            </button>
            <button type="button" @click="goBack()" :disabled="currentPath === ''" class="rounded bg-gray-500 px-3 py-1 text-white transition hover:bg-gray-600 disabled:cursor-not-allowed disabled:opacity-50" title="Назад">
                <i class="fas fa-arrow-left"></i>
            </button>
            <div class="min-w-[220px] flex-1 rounded bg-gray-100 px-3 py-2">
                <span class="text-gray-600">Поточний шлях:</span>
                <span class="ml-2 font-mono" x-text="currentPath || '/'"></span>
            </div>
            <button type="button" @click="refreshCurrentView()" class="rounded bg-green-500 px-3 py-1 text-white transition hover:bg-green-600" title="Оновити">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 xl:grid-cols-[minmax(0,2fr)_minmax(320px,1fr)]">
        <div class="rounded-lg bg-white shadow">
            <div class="border-b p-4">
                <h2 class="text-xl font-semibold text-gray-800">
                    <i class="fas fa-folder-tree mr-2"></i>Структура файлів
                </h2>
            </div>
            <div class="p-4">
                <div x-show="missingTargetNotice" x-cloak class="mb-4 rounded border border-amber-300 bg-amber-50 px-4 py-3 text-amber-800">
                    <i class="fas fa-triangle-exclamation mr-2"></i>
                    Невірний прямий шлях:
                    <code class="break-all font-mono" x-text="missingTargetNotice"></code>
                </div>

                <div x-show="loading" class="py-8 text-center">
                    <i class="fas fa-spinner fa-spin text-4xl text-blue-500"></i>
                    <p class="mt-2 text-gray-600">Завантаження...</p>
                </div>

                <div x-show="error" x-cloak class="rounded border border-red-400 bg-red-100 px-4 py-3 text-red-700">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <span x-text="error"></span>
                </div>

                <div x-show="!loading && !error" x-cloak class="space-y-1">
                    <template x-if="items.length === 0">
                        <p class="py-8 text-center text-gray-500">Папка порожня або недоступна</p>
                    </template>

                    <template x-for="item in items" :key="item.path">
                        <div
                            class="flex items-center gap-3 rounded border border-transparent p-3 transition hover:border-gray-200 hover:bg-gray-50"
                            :class="selectedItem && selectedItem.path === item.path ? 'border-blue-200 bg-blue-50' : ''"
                            @click="selectItem(item)"
                            @dblclick="openItem(item)"
                        >
                            <div class="w-8 text-center">
                                <i :class="item.type === 'directory' ? 'fas fa-folder text-xl text-yellow-500' : 'fas fa-file text-gray-500 ' + getFileIcon(item)"></i>
                            </div>

                            <div class="min-w-0 flex-1">
                                <button
                                    type="button"
                                    class="truncate text-left font-medium text-gray-800 transition hover:text-blue-700 hover:underline"
                                    @click.stop="openItem(item)"
                                    x-text="item.name"
                                ></button>
                                <p class="mt-1 truncate text-xs text-gray-500" x-text="item.path"></p>
                            </div>

                            <div class="w-24 text-right text-sm text-gray-500">
                                <span x-text="item.type === 'file' ? formatFileSize(item.size) : ''"></span>
                            </div>

                            <div class="flex gap-1">
                                <button
                                    type="button"
                                    @click.stop="openItem(item)"
                                    class="rounded p-2 text-blue-600 transition hover:bg-blue-100"
                                    :title="item.type === 'directory' ? 'Відкрити папку' : 'Відкрити в popup'"
                                >
                                    <i :class="item.type === 'directory' ? 'fas fa-folder-open' : 'fas fa-arrow-up-right-from-square'"></i>
                                </button>
                                <template x-if="item.type === 'file'">
                                    <button
                                        type="button"
                                        @click.stop="openStandaloneFile(item.path)"
                                        class="rounded p-2 text-emerald-600 transition hover:bg-emerald-100"
                                        title="Відкрити окремо"
                                    >
                                        <i class="fas fa-up-right-from-square"></i>
                                    </button>
                                </template>
                                <template x-if="item.type === 'file'">
                                    <button
                                        type="button"
                                        @click.stop="downloadFile(item.path)"
                                        class="rounded p-2 text-purple-600 transition hover:bg-purple-100"
                                        title="Завантажити"
                                    >
                                        <i class="fas fa-download"></i>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <div class="rounded-lg bg-white shadow">
            <div class="border-b p-4">
                <h2 class="text-xl font-semibold text-gray-800">
                    <i class="fas fa-info-circle mr-2"></i>Інформація
                </h2>
            </div>
            <div class="space-y-4 p-4">
                <div>
                    <label class="text-sm font-semibold text-gray-600">Назва:</label>
                    <p class="break-all text-gray-800" x-text="inspectorItem.name"></p>
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-600">Тип:</label>
                    <p class="text-gray-800" x-text="inspectorItem.type === 'directory' ? 'Директорія' : 'Файл'"></p>
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-600">Шлях:</label>
                    <p class="break-all font-mono text-sm text-gray-800" x-text="inspectorItem.path || '/'"></p>
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-600">Розмір:</label>
                    <p class="text-gray-800" x-text="inspectorItem.type === 'file' ? formatFileSize(inspectorItem.size) : '—'"></p>
                </div>
                <div x-show="inspectorItem.type === 'file' && inspectorItem.extension">
                    <label class="text-sm font-semibold text-gray-600">Розширення:</label>
                    <p class="text-gray-800" x-text="inspectorItem.extension"></p>
                </div>
                <div x-show="inspectorItem.mime_type">
                    <label class="text-sm font-semibold text-gray-600">MIME тип:</label>
                    <p class="break-all text-sm text-gray-800" x-text="inspectorItem.mime_type"></p>
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-600">Змінено:</label>
                    <p class="text-gray-800" x-text="formatDate(inspectorItem.modified)"></p>
                </div>

                <div class="border-t pt-4">
                    <template x-if="inspectorItem.type === 'directory'">
                        <button type="button" @click="navigateToPath(inspectorItem.path)" class="w-full rounded bg-blue-500 px-3 py-2 text-white transition hover:bg-blue-600">
                            <i class="fas fa-folder-open mr-2"></i>Відкрити папку
                        </button>
                    </template>

                    <template x-if="inspectorItem.type === 'file'">
                        <div class="space-y-2">
                            <button type="button" @click="openEmbeddedFile(inspectorItem.path)" class="w-full rounded bg-orange-500 px-3 py-2 text-white transition hover:bg-orange-600">
                                <i class="fas fa-file-pen mr-2"></i>Відкрити в popup через AJAX
                            </button>
                            <button type="button" @click="openStandaloneFile(inspectorItem.path)" class="w-full rounded bg-emerald-500 px-3 py-2 text-white transition hover:bg-emerald-600">
                                <i class="fas fa-up-right-from-square mr-2"></i>Відкрити окремо
                            </button>
                            <button type="button" @click="downloadFile(inspectorItem.path)" class="w-full rounded bg-purple-500 px-3 py-2 text-white transition hover:bg-purple-600">
                                <i class="fas fa-download mr-2"></i>Завантажити
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <div
        x-show="fileModalOpen"
        x-cloak
        class="fixed inset-0 z-[90] flex items-center justify-center p-4"
        aria-modal="true"
        role="dialog"
    >
        <div class="absolute inset-0 bg-slate-900/70 backdrop-blur-sm" @click="closeFileModal()"></div>

        <div class="relative z-10 flex h-[90vh] max-h-[90vh] w-full max-w-7xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
            <div class="border-b border-slate-200 bg-slate-50 px-5 py-4">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                    <div class="min-w-0">
                        <h2 class="truncate text-xl font-semibold text-slate-900">AJAX popup редактор</h2>
                        <p class="mt-1 break-all font-mono text-sm text-slate-500" x-text="openedFilePath || 'Файл не вибрано'"></p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button
                            type="button"
                            @click="copyOpenedFileUrl()"
                            :disabled="embedLoading || !openedFilePath"
                            class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <i class="fas fa-copy mr-2"></i>Копіювати URL
                        </button>
                        <button
                            type="button"
                            @click="reloadOpenedFile()"
                            :disabled="embedLoading || !openedFilePath"
                            class="rounded-lg bg-slate-700 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <i class="fas fa-rotate mr-2"></i>Перечитати
                        </button>
                        <button
                            type="button"
                            @click="saveOpenedFile()"
                            :disabled="embedLoading || !embedUi.canSave"
                            class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <i class="fas fa-save mr-2"></i><span x-text="embedUi.saving ? 'Збереження...' : 'Зберегти'"></span>
                        </button>
                        <button type="button" @click="openStandaloneFile(openedFilePath)" class="rounded-lg bg-cyan-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-cyan-700">
                            <i class="fas fa-up-right-from-square mr-2"></i>Окремо
                        </button>
                        <button type="button" @click="closeFileModal()" class="rounded-lg bg-gray-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-gray-600">
                            <i class="fas fa-times mr-2"></i>Закрити
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex-1 min-h-0 overflow-hidden bg-slate-100 p-4">
                <div x-show="embedError" x-cloak class="mb-4 rounded border border-red-300 bg-red-50 px-4 py-3 text-red-800">
                    <i class="fas fa-triangle-exclamation mr-2"></i><span x-text="embedError"></span>
                </div>

                <div x-show="embedLoading" x-cloak class="flex h-full min-h-[320px] items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin text-4xl text-blue-500"></i>
                        <p class="mt-3 text-slate-600">Підвантаження embeddable-редактора...</p>
                    </div>
                </div>

                <div x-show="!embedLoading && !embedError" x-cloak class="h-full min-h-0">
                    <div x-ref="embedModalContainer" class="h-full min-h-0"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('head-scripts')
<script defer src="{{ route('file-manager.embed.bootstrap') }}"></script>
@endpush

@push('scripts')
<script>
@include('file-manager::partials.modal-helper-js-code')

const FILE_MANAGER_INDEX_URL = @js($managerIndexUrl);
const FILE_MANAGER_OPEN_BASE_URL = @js($managerOpenBaseUrl);
const FILE_MANAGER_EMBED_URL = @js($embedPageUrl);
const FILE_MANAGER_EMBED_BOOTSTRAP_URL = @js($embedBootstrapUrl);

function fileManager(initialPath = '', initialSelection = '', initialTarget = '', initialMissingTarget = '') {
    return {
        initialPath,
        initialSelection,
        initialTarget,
        initialMissingTarget,
        currentPath: '',
        items: [],
        selectedItem: null,
        loading: false,
        error: null,
        missingTargetNotice: initialMissingTarget || '',
        pathHistory: [],
        activeTargetPath: '',
        fileModalOpen: false,
        openedFilePath: '',
        embedLoading: false,
        embedError: null,
        embedUi: {
            ready: false,
            canSave: false,
            saving: false,
            loading: false,
            dirty: false,
            writable: false,
            isText: false,
            standaloneUrl: '',
        },
        embedStateRoot: null,
        embedStateHandler: null,

        get inspectorItem() {
            return this.selectedItem || this.buildDirectoryDescriptor(this.currentPath);
        },

        async init() {
            const loaded = await this.loadTree(this.initialPath || '');

            if (!loaded) {
                return;
            }

            if (this.initialSelection) {
                await this.openEmbeddedFile(this.initialSelection, {
                    syncUrl: false,
                    warnOnUnsaved: false,
                });
            } else {
                this.syncCurrentUrl(this.initialTarget || this.currentPath);
            }
        },

        nextTickAsync() {
            return new Promise((resolve) => this.$nextTick(resolve));
        },

        async ensureEmbedModuleLoaded(timeout = 10000) {
            if (window.FileManagerEmbed?.loadInto) {
                return true;
            }

            if (!window.__fileManagerEmbedBootstrapPromise) {
                window.__fileManagerEmbedBootstrapPromise = new Promise((resolve, reject) => {
                    const existingScript = document.querySelector(`script[data-file-manager-embed-bootstrap="true"]`);

                    if (existingScript) {
                        existingScript.addEventListener('load', () => resolve(true), { once: true });
                        existingScript.addEventListener('error', () => reject(new Error('Не вдалося завантажити bootstrap embeddable-модуля.')), { once: true });
                        return;
                    }

                    const script = document.createElement('script');
                    script.src = FILE_MANAGER_EMBED_BOOTSTRAP_URL;
                    script.defer = true;
                    script.dataset.fileManagerEmbedBootstrap = 'true';
                    script.addEventListener('load', () => resolve(true), { once: true });
                    script.addEventListener('error', () => reject(new Error('Не вдалося завантажити bootstrap embeddable-модуля.')), { once: true });
                    document.head.appendChild(script);
                });
            }

            await Promise.race([
                window.__fileManagerEmbedBootstrapPromise,
                new Promise((_, reject) => window.setTimeout(() => reject(new Error('Час очікування embeddable-модуля вичерпано.')), timeout)),
            ]);

            if (!window.FileManagerEmbed?.loadInto) {
                throw new Error('Embeddable-модуль завантажився некоректно.');
            }

            return true;
        },

        async loadTree(path, options = {}) {
            const normalizedPath = this.normalizePath(path);
            this.loading = true;
            this.error = null;

            try {
                const response = await fetch(`{{ route('file-manager.tree') }}?path=${encodeURIComponent(normalizedPath)}`, {
                    headers: { Accept: 'application/json' },
                });
                const data = await response.json();

                if (!data.success) {
                    this.error = data.error || 'Помилка завантаження';
                    return false;
                }

                this.items = Array.isArray(data.items) ? data.items : [];
                this.currentPath = this.normalizePath(data.path || normalizedPath);

                if (options.selectedPath) {
                    this.setSelectedItemByPath(options.selectedPath);
                } else if (this.selectedItem?.type === 'file') {
                    this.setSelectedItemByPath(this.selectedItem.path);
                } else if (this.selectedItem?.type !== 'directory') {
                    this.selectedItem = null;
                }

                return true;
            } catch (error) {
                this.error = 'Помилка з\'єднання з сервером';
                console.error(error);
                return false;
            } finally {
                this.loading = false;
            }
        },

        selectItem(item) {
            this.selectedItem = item;
        },

        async openItem(item) {
            if (!item) {
                return;
            }

            if (item.type === 'directory') {
                await this.navigateToPath(item.path);
                return;
            }

            await this.openEmbeddedFile(item.path);
        },

        async navigateToPath(path) {
            if (!(await this.confirmDiscardChanges())) {
                return;
            }

            const normalizedPath = this.normalizePath(path);
            this.pathHistory.push(this.currentPath);
            this.selectedItem = this.buildDirectoryDescriptor(normalizedPath);
            this.resetModalState();

            const loaded = await this.loadTree(normalizedPath);

            if (loaded) {
                this.syncCurrentUrl(normalizedPath);
            }
        },

        async navigateToRoot() {
            if (!(await this.confirmDiscardChanges())) {
                return;
            }

            this.pathHistory = [];
            this.selectedItem = this.buildDirectoryDescriptor('');
            this.resetModalState();

            const loaded = await this.loadTree('');

            if (loaded) {
                this.syncCurrentUrl('');
            }
        },

        async goBack() {
            if (!(await this.confirmDiscardChanges())) {
                return;
            }

            let previousPath = '';

            if (this.pathHistory.length > 0) {
                previousPath = this.pathHistory.pop();
            } else if (this.currentPath !== '') {
                previousPath = this.directoryOf(this.currentPath);
            } else {
                return;
            }

            this.selectedItem = this.buildDirectoryDescriptor(previousPath);
            this.resetModalState();

            const loaded = await this.loadTree(previousPath);

            if (loaded) {
                this.syncCurrentUrl(previousPath);
            }
        },

        async refreshCurrentView() {
            if (this.fileModalOpen && this.openedFilePath && !(await this.confirmDiscardChanges())) {
                return;
            }

            const openedPath = this.fileModalOpen ? this.openedFilePath : '';
            const directoryPath = openedPath ? this.directoryOf(openedPath) : this.currentPath;
            const loaded = await this.loadTree(directoryPath, {
                selectedPath: openedPath || this.selectedItem?.path || '',
            });

            if (!loaded) {
                return;
            }

            if (openedPath) {
                await this.openEmbeddedFile(openedPath, {
                    syncUrl: false,
                    warnOnUnsaved: false,
                });
                this.syncCurrentUrl(openedPath);
            } else {
                this.syncCurrentUrl(this.currentPath);
            }
        },

        async openEmbeddedFile(path, options = {}) {
            const normalizedPath = this.normalizePath(path);

            if (!normalizedPath) {
                return;
            }

            if (options.warnOnUnsaved !== false && !(await this.confirmDiscardChanges())) {
                return;
            }

            const parentPath = this.directoryOf(normalizedPath);

            if (parentPath !== this.currentPath) {
                const loadedParent = await this.loadTree(parentPath, {
                    selectedPath: normalizedPath,
                });

                if (!loadedParent) {
                    return;
                }
            } else {
                this.setSelectedItemByPath(normalizedPath);
            }

            this.fileModalOpen = true;
            this.openedFilePath = normalizedPath;
            this.embedLoading = true;
            this.embedError = null;
            this.resetEmbedUi();

            await this.nextTickAsync();

            if (this.$refs.embedModalContainer) {
                this.detachEmbedStateListener();
                this.$refs.embedModalContainer.innerHTML = '';
            }

            try {
                await this.ensureEmbedModuleLoaded();
                await window.FileManagerEmbed.loadInto(this.$refs.embedModalContainer, normalizedPath, {
                    warnOnUnsaved: false,
                });
                this.bindEmbedStateListener();
                this.syncEmbedUiStateFromInstance();

                if (options.syncUrl !== false) {
                    this.syncCurrentUrl(normalizedPath);
                }
            } catch (error) {
                this.embedError = error instanceof Error ? error.message : 'Не вдалося відкрити файл через AJAX-модуль.';
                console.error(error);
            } finally {
                this.embedLoading = false;
            }
        },

        async reloadOpenedFile() {
            const instance = this.getEmbedInstance();

            if (!this.openedFilePath || !instance || typeof instance.reloadCurrentFile !== 'function') {
                return;
            }

            await instance.reloadCurrentFile();
            this.syncEmbedUiStateFromInstance();
            this.syncCurrentUrl(this.openedFilePath);
        },

        async saveOpenedFile() {
            const instance = this.getEmbedInstance();

            if (!instance || typeof instance.save !== 'function') {
                return;
            }

            await instance.save();
            this.syncEmbedUiStateFromInstance();
        },

        async copyOpenedFileUrl() {
            const instance = this.getEmbedInstance();

            if (instance && typeof instance.copyCurrentUrl === 'function') {
                await instance.copyCurrentUrl();
                return;
            }

            if (!this.openedFilePath) {
                return;
            }

            try {
                await navigator.clipboard.writeText(this.buildStandaloneEmbedUrl(this.openedFilePath));
            } catch (error) {
                console.error(error);
            }
        },

        async closeFileModal() {
            if (!(await this.confirmDiscardChanges())) {
                return;
            }

            this.resetModalState();
            this.syncCurrentUrl(this.currentPath);
        },

        resetModalState() {
            this.detachEmbedStateListener();
            this.resetEmbedUi();
            this.fileModalOpen = false;
            this.openedFilePath = '';
            this.embedLoading = false;
            this.embedError = null;

            if (this.$refs.embedModalContainer) {
                this.$refs.embedModalContainer.innerHTML = '';
            }
        },

        getEmbedInstance() {
            const root = this.$refs.embedModalContainer?.querySelector('[data-file-manager-embed-root]');
            return root?.__fileManagerEmbed || null;
        },

        bindEmbedStateListener() {
            const root = this.$refs.embedModalContainer?.querySelector('[data-file-manager-embed-root]');

            if (!root) {
                return;
            }

            this.detachEmbedStateListener();
            this.embedStateRoot = root;
            this.embedStateHandler = (event) => {
                this.applyEmbedUiState(event.detail || {});
            };
            root.addEventListener('file-manager-embed:state', this.embedStateHandler);
        },

        detachEmbedStateListener() {
            if (this.embedStateRoot && this.embedStateHandler) {
                this.embedStateRoot.removeEventListener('file-manager-embed:state', this.embedStateHandler);
            }

            this.embedStateRoot = null;
            this.embedStateHandler = null;
        },

        resetEmbedUi() {
            this.embedUi = {
                ready: false,
                canSave: false,
                saving: false,
                loading: false,
                dirty: false,
                writable: false,
                isText: false,
                standaloneUrl: '',
            };
        },

        applyEmbedUiState(state = {}) {
            this.embedUi = {
                ready: true,
                canSave: !!state.canSave,
                saving: !!state.saving,
                loading: !!state.loading,
                dirty: !!state.dirty,
                writable: !!state.writable,
                isText: !!state.isText,
                standaloneUrl: state.standaloneUrl || '',
            };
        },

        syncEmbedUiStateFromInstance() {
            const instance = this.getEmbedInstance();

            if (!instance || typeof instance.getUiState !== 'function') {
                this.resetEmbedUi();
                return;
            }

            this.applyEmbedUiState(instance.getUiState());
        },

        async confirmDiscardChanges(message = 'Є незбережені зміни. Продовжити без збереження?') {
            const instance = this.getEmbedInstance();

            if (!instance || typeof instance.confirmDiscardChanges !== 'function') {
                return true;
            }

            return instance.confirmDiscardChanges(message);
        },

        setSelectedItemByPath(path) {
            const normalizedPath = this.normalizePath(path);
            this.selectedItem = this.items.find((item) => item.path === normalizedPath) || null;
            return this.selectedItem;
        },

        openStandaloneFile(path) {
            const normalizedPath = this.normalizePath(path);

            if (!normalizedPath) {
                return;
            }

            window.open(this.buildStandaloneEmbedUrl(normalizedPath), '_blank', 'noopener');
        },

        downloadFile(path) {
            if (!path) {
                return;
            }

            window.location.href = `{{ route('file-manager.download') }}?path=${encodeURIComponent(path)}`;
        },

        buildDirectoryDescriptor(path = '') {
            const normalizedPath = this.normalizePath(path);

            return {
                path: normalizedPath,
                name: normalizedPath ? this.basename(normalizedPath) : '/',
                type: 'directory',
                size: null,
                modified: null,
                readable: true,
                writable: true,
            };
        },

        normalizePath(path = '') {
            return String(path || '').trim().replace(/\\/g, '/').replace(/^\/+|\/+$/g, '');
        },

        basename(path = '') {
            const normalizedPath = this.normalizePath(path);

            if (!normalizedPath) {
                return '/';
            }

            return normalizedPath.split('/').pop() || normalizedPath;
        },

        directoryOf(path = '') {
            const normalizedPath = this.normalizePath(path);

            if (!normalizedPath || !normalizedPath.includes('/')) {
                return '';
            }

            return normalizedPath.split('/').slice(0, -1).join('/');
        },

        buildTargetUrl(path = '') {
            const normalizedPath = this.normalizePath(path);

            if (!normalizedPath) {
                return FILE_MANAGER_INDEX_URL;
            }

            return `${FILE_MANAGER_OPEN_BASE_URL}/${normalizedPath.split('/').map((segment) => encodeURIComponent(segment)).join('/')}`;
        },

        buildStandaloneEmbedUrl(path = '') {
            const normalizedPath = this.normalizePath(path);
            const url = new URL(FILE_MANAGER_EMBED_URL, window.location.origin);

            if (normalizedPath) {
                url.searchParams.set('path', normalizedPath);
            }

            return url.toString();
        },

        syncCurrentUrl(path = '') {
            const normalizedPath = this.normalizePath(path);
            this.activeTargetPath = normalizedPath;

            if (window.history?.replaceState) {
                window.history.replaceState({ targetPath: normalizedPath }, '', this.buildTargetUrl(normalizedPath));
            }
        },

        formatFileSize(bytes) {
            if (bytes === null || bytes === undefined) {
                return 'N/A';
            }

            const units = ['B', 'KB', 'MB', 'GB', 'TB'];
            let size = Number(bytes);
            let unitIndex = 0;

            while (size >= 1024 && unitIndex < units.length - 1) {
                size /= 1024;
                unitIndex += 1;
            }

            return `${size.toFixed(unitIndex === 0 ? 0 : 2)} ${units[unitIndex]}`;
        },

        formatDate(timestamp) {
            if (!timestamp) {
                return 'N/A';
            }

            return new Date(timestamp * 1000).toLocaleString('uk-UA');
        },

        getFileIcon(item) {
            const ext = item.extension ? item.extension.toLowerCase() : '';
            const iconMap = {
                php: 'fa-file-code text-purple-600',
                js: 'fa-file-code text-yellow-600',
                json: 'fa-file-code text-green-600',
                html: 'fa-file-code text-orange-600',
                css: 'fa-file-code text-blue-600',
                md: 'fa-file-lines text-gray-600',
                txt: 'fa-file-lines text-gray-600',
                env: 'fa-file-lines text-gray-600',
                yml: 'fa-file-lines text-cyan-600',
                yaml: 'fa-file-lines text-cyan-600',
                xml: 'fa-file-code text-pink-600',
                sql: 'fa-database text-indigo-600',
                png: 'fa-file-image text-pink-600',
                jpg: 'fa-file-image text-pink-600',
                jpeg: 'fa-file-image text-pink-600',
                gif: 'fa-file-image text-pink-600',
                svg: 'fa-file-image text-pink-600',
                pdf: 'fa-file-pdf text-red-600',
                zip: 'fa-file-archive text-gray-700',
                tar: 'fa-file-archive text-gray-700',
                gz: 'fa-file-archive text-gray-700',
            };

            return iconMap[ext] || 'text-gray-500';
        },
    };
}
</script>
@endpush
