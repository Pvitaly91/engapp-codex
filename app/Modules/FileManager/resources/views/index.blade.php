@extends('layouts.app')

@section('title', 'Файловий менеджер')

@section('content')
@php
    $managerIndexUrl = route('file-manager.index');
    $managerOpenBaseUrl = rtrim($managerIndexUrl, '/').'/open';
@endphp

<div class="container mx-auto px-4" x-data="fileManager(@js($initialPath ?? ''), @js($initialSelection ?? ''), @js($initialTarget ?? ''), @js($initialMissingTarget ?? ''))" x-init="init()">
    <div class="mb-6">
        <div class="mb-2 flex items-center justify-between">
            <h1 class="text-3xl font-bold text-gray-800">Файловий менеджер</h1>
            <div class="inline-flex rounded-lg border border-gray-300 bg-white p-1 shadow-sm">
                <button class="cursor-default rounded-md bg-blue-600 px-4 py-2 font-medium text-white">
                    <i class="fas fa-list mr-2"></i>Файловий менеджер
                </button>
                <a href="{{ route('file-manager.ide') }}" class="rounded-md px-4 py-2 font-medium text-gray-700 transition hover:bg-gray-100">
                    <i class="fas fa-code mr-2"></i>IDE режим
                </a>
            </div>
        </div>
        <p class="text-gray-600">Новий режим відкриття файлів без старого modal-редактора.</p>
        <p class="mt-1 text-sm text-gray-500">Базова директорія: <code class="rounded bg-gray-100 px-2 py-1">{{ $basePath }}</code></p>
    </div>

    <div class="mb-4 rounded-lg bg-white p-4 shadow">
        <div class="flex items-center gap-2 text-sm">
            <button type="button" @click="navigateToRoot()" class="rounded bg-blue-500 px-3 py-1 text-white transition hover:bg-blue-600" title="Кореневий каталог">
                <i class="fas fa-home"></i>
            </button>
            <button type="button" @click="goBack()" :disabled="currentPath === ''" class="rounded bg-gray-500 px-3 py-1 text-white transition hover:bg-gray-600 disabled:cursor-not-allowed disabled:opacity-50" title="Назад">
                <i class="fas fa-arrow-left"></i>
            </button>
            <div class="flex-1 rounded bg-gray-100 px-3 py-2">
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
                        <div class="flex items-center gap-3 rounded border border-transparent p-3 transition hover:border-gray-200 hover:bg-gray-50" :class="selectedItem && selectedItem.path === item.path ? 'border-blue-200 bg-blue-50' : ''" @click="selectItem(item)" @dblclick="openItem(item)">
                            <div class="w-8 text-center">
                                <i :class="item.type === 'directory' ? 'fas fa-folder text-xl text-yellow-500' : 'fas fa-file text-gray-500 ' + getFileIcon(item)"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <button type="button" class="truncate text-left font-medium text-gray-800 transition hover:text-blue-700 hover:underline" @click.stop="openItem(item)" x-text="item.name"></button>
                                <p class="mt-1 truncate text-xs text-gray-500" x-text="item.path"></p>
                            </div>
                            <div class="w-24 text-right text-sm text-gray-500">
                                <span x-text="item.type === 'file' ? formatFileSize(item.size) : ''"></span>
                            </div>
                            <div class="flex gap-1">
                                <button type="button" @click.stop="openItem(item)" class="rounded p-2 text-blue-600 transition hover:bg-blue-100" :title="item.type === 'directory' ? 'Відкрити папку' : 'Відкрити файл'">
                                    <i :class="item.type === 'directory' ? 'fas fa-folder-open' : 'fas fa-arrow-up-right-from-square'"></i>
                                </button>
                                <template x-if="item.type === 'file'">
                                    <button type="button" @click.stop="downloadFile(item.path)" class="rounded p-2 text-purple-600 transition hover:bg-purple-100" title="Завантажити">
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
                            <button type="button" @click="openFile(inspectorItem.path)" class="w-full rounded bg-orange-500 px-3 py-2 text-white transition hover:bg-orange-600">
                                <i class="fas fa-file-pen mr-2"></i>Відкрити файл
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

    <div class="mt-4 rounded-lg bg-white shadow" x-show="filePanelVisible" x-cloak>
        <div class="border-b p-4">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-3">
                        <h2 class="truncate text-xl font-semibold text-gray-800" x-text="openedFile.name || 'Файл'"></h2>
                        <span x-show="isOpenedFileDirty()" class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Незбережені зміни</span>
                        <span x-show="!openedFile.editable && openedFile.isText" class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">Режим перегляду</span>
                    </div>
                    <p class="mt-2 break-all font-mono text-sm text-gray-500" x-text="openedFile.path"></p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="button" @click="saveOpenedFile()" :disabled="fileSaving || !openedFile.editable || !isOpenedFileDirty()" class="rounded bg-green-500 px-4 py-2 text-white transition hover:bg-green-600 disabled:cursor-not-allowed disabled:opacity-50">
                        <i class="fas fa-save mr-2"></i><span x-text="fileSaving ? 'Збереження...' : 'Зберегти'"></span>
                    </button>
                    <button type="button" @click="reloadOpenedFile()" class="rounded bg-slate-600 px-4 py-2 text-white transition hover:bg-slate-700">
                        <i class="fas fa-rotate mr-2"></i>Перезавантажити
                    </button>
                    <button type="button" @click="downloadFile(openedFile.path)" class="rounded bg-purple-500 px-4 py-2 text-white transition hover:bg-purple-600">
                        <i class="fas fa-download mr-2"></i>Завантажити
                    </button>
                    <button type="button" @click="closeOpenedFile()" class="rounded bg-gray-500 px-4 py-2 text-white transition hover:bg-gray-600">
                        <i class="fas fa-times mr-2"></i>Закрити
                    </button>
                </div>
            </div>
        </div>
        <div class="p-4">
            <div x-show="fileMessage" x-cloak class="mb-4 rounded border border-green-300 bg-green-50 px-4 py-3 text-green-800">
                <i class="fas fa-circle-check mr-2"></i><span x-text="fileMessage"></span>
            </div>
            <div x-show="fileLoading" class="flex min-h-[320px] items-center justify-center rounded border border-dashed border-gray-300 bg-gray-50">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin text-4xl text-blue-500"></i>
                    <p class="mt-3 text-gray-600">Відкриття файлу...</p>
                </div>
            </div>
            <div x-show="!fileLoading && fileError" x-cloak class="rounded border border-red-300 bg-red-50 px-4 py-3 text-red-800">
                <i class="fas fa-triangle-exclamation mr-2"></i><span x-text="fileError"></span>
            </div>
            <div x-show="!fileLoading && !fileError && openedFile.isText && openedFile.editable" x-cloak>
                <textarea x-model="openedFile.content" @input="handleOpenedFileInput()" class="fm-editor-textarea" spellcheck="false"></textarea>
            </div>
            <div x-show="!fileLoading && !fileError && openedFile.isText && !openedFile.editable" x-cloak>
                <pre class="fm-viewer-pre" x-text="openedFile.content"></pre>
            </div>
            <div x-show="!fileLoading && !fileError && !openedFile.isText" x-cloak class="rounded border border-slate-200 bg-slate-50 px-6 py-8 text-center text-slate-700">
                <i class="fas fa-file text-4xl"></i>
                <p class="mt-4 text-lg font-semibold">Цей файл не можна відкрити як текст</p>
                <p class="mt-2 text-sm">Доступне лише завантаження.</p>
            </div>
        </div>
    </div>
</div>

<script>
const FILE_MANAGER_INDEX_URL = @js($managerIndexUrl);
const FILE_MANAGER_OPEN_BASE_URL = @js($managerOpenBaseUrl);

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
        filePanelVisible: false,
        fileLoading: false,
        fileSaving: false,
        fileError: null,
        fileMessage: null,
        originalFileContent: '',
        openedFile: { path: '', name: '', extension: '', mimeType: '', size: null, isText: true, editable: false, content: '' },

        get inspectorItem() {
            return this.selectedItem || this.buildDirectoryDescriptor(this.currentPath);
        },

        async init() {
            const loaded = await this.loadTree(this.initialPath || '');
            if (!loaded) return;
            if (this.initialSelection) {
                await this.openFile(this.initialSelection, { syncUrl: false, ignoreUnsaved: true });
            } else {
                this.syncCurrentUrl(this.initialTarget || this.currentPath);
            }
        },

        async loadTree(path, options = {}) {
            const normalizedPath = this.normalizePath(path);
            this.loading = true;
            this.error = null;
            try {
                const response = await fetch(`{{ route('file-manager.tree') }}?path=${encodeURIComponent(normalizedPath)}`, { headers: { Accept: 'application/json' } });
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
            } catch (e) {
                this.error = 'Помилка з\'єднання з сервером';
                console.error(e);
                return false;
            } finally {
                this.loading = false;
            }
        },

        selectItem(item) {
            this.selectedItem = item;
        },

        async openItem(item) {
            if (!item) return;
            if (item.type === 'directory') {
                await this.navigateToPath(item.path);
                return;
            }
            await this.openFile(item.path);
        },

        async navigateToPath(path) {
            if (!this.confirmDiscardChanges()) return;
            const normalizedPath = this.normalizePath(path);
            this.pathHistory.push(this.currentPath);
            this.selectedItem = this.buildDirectoryDescriptor(normalizedPath);
            this.resetOpenedFile();
            const loaded = await this.loadTree(normalizedPath);
            if (loaded) this.syncCurrentUrl(normalizedPath);
        },

        async navigateToRoot() {
            if (!this.confirmDiscardChanges()) return;
            this.pathHistory = [];
            this.selectedItem = this.buildDirectoryDescriptor('');
            this.resetOpenedFile();
            const loaded = await this.loadTree('');
            if (loaded) this.syncCurrentUrl('');
        },

        async goBack() {
            if (!this.confirmDiscardChanges()) return;
            let previousPath = '';
            if (this.pathHistory.length > 0) previousPath = this.pathHistory.pop();
            else if (this.currentPath !== '') previousPath = this.directoryOf(this.currentPath);
            else return;
            this.selectedItem = this.buildDirectoryDescriptor(previousPath);
            this.resetOpenedFile();
            const loaded = await this.loadTree(previousPath);
            if (loaded) this.syncCurrentUrl(previousPath);
        },

        async refreshCurrentView() {
            const openedPath = this.filePanelVisible ? this.openedFile.path : '';
            const directoryPath = openedPath ? this.directoryOf(openedPath) : this.currentPath;
            const loaded = await this.loadTree(directoryPath, { selectedPath: openedPath || this.selectedItem?.path || '' });
            if (!loaded) return;
            if (openedPath) {
                await this.openFile(openedPath, { syncUrl: false, ignoreUnsaved: true });
                this.syncCurrentUrl(openedPath);
            } else {
                this.syncCurrentUrl(this.currentPath);
            }
        },

        async openFile(path, options = {}) {
            const normalizedPath = this.normalizePath(path);
            if (!normalizedPath) return;
            if (!options.ignoreUnsaved && !this.confirmDiscardChanges()) return;
            const parentPath = this.directoryOf(normalizedPath);
            if (parentPath !== this.currentPath) {
                const loadedParent = await this.loadTree(parentPath, { selectedPath: normalizedPath });
                if (!loadedParent) return;
            } else {
                this.setSelectedItemByPath(normalizedPath);
            }
            const item = this.selectedItem || this.buildFileDescriptor(normalizedPath);
            this.filePanelVisible = true;
            this.fileLoading = true;
            this.fileSaving = false;
            this.fileError = null;
            this.fileMessage = null;
            this.openedFile = { path: normalizedPath, name: item.name || this.basename(normalizedPath), extension: this.getExtensionFromPath(normalizedPath), mimeType: item.mime_type || '', size: item.size ?? null, isText: true, editable: false, content: '' };
            this.originalFileContent = '';
            try {
                const editableLoaded = await this.loadEditableFile(normalizedPath, item);
                if (!editableLoaded) await this.loadReadonlyFile(normalizedPath, item);
            } finally {
                this.fileLoading = false;
            }
            if (options.syncUrl !== false) this.syncCurrentUrl(normalizedPath);
        },

        async loadEditableFile(path, item) {
            try {
                const response = await fetch(`{{ route('file-manager.content') }}?path=${encodeURIComponent(path)}`, { headers: { Accept: 'application/json' } });
                const data = await response.json();
                if (!response.ok || !data.success) return false;
                this.openedFile = { path: data.content.path, name: data.content.name, extension: this.getExtensionFromPath(data.content.path), mimeType: data.content.mime_type || item.mime_type || '', size: data.content.size ?? item.size ?? null, isText: true, editable: item.writable !== false, content: data.content.content || '' };
                this.originalFileContent = this.openedFile.content;
                return true;
            } catch (e) {
                console.error(e);
                return false;
            }
        },

        async loadReadonlyFile(path, item) {
            try {
                const response = await fetch(`{{ route('file-manager.preview') }}?path=${encodeURIComponent(path)}`, { headers: { Accept: 'application/json' } });
                const data = await response.json();
                if (!response.ok || !data.success) {
                    this.fileError = data?.error || 'Не вдалося відкрити файл';
                    this.openedFile = { path, name: item.name || this.basename(path), extension: this.getExtensionFromPath(path), mimeType: item.mime_type || '', size: item.size ?? null, isText: false, editable: false, content: '' };
                    return;
                }
                this.openedFile = { path: data.content.path, name: data.content.name, extension: this.getExtensionFromPath(data.content.path), mimeType: data.content.mime_type || item.mime_type || '', size: data.content.size ?? item.size ?? null, isText: !!data.content.is_text, editable: false, content: data.content.is_text ? (data.content.content || '') : '' };
                this.originalFileContent = this.openedFile.content;
                this.fileMessage = this.openedFile.isText ? 'Файл відкрито в режимі перегляду.' : 'Файл не є текстовим. Доступне лише завантаження.';
            } catch (e) {
                this.fileError = 'Помилка з\'єднання з сервером';
                console.error(e);
            }
        },

        async saveOpenedFile() {
            if (!this.openedFile.path || !this.openedFile.editable) return;
            this.fileSaving = true;
            this.fileError = null;
            this.fileMessage = null;
            try {
                const response = await fetch(`{{ route('file-manager.update') }}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': this.getCsrfToken() },
                    body: JSON.stringify({ path: this.openedFile.path, content: this.openedFile.content }),
                });
                const data = await response.json();
                if (!response.ok || !data.success) {
                    this.fileError = data.error || 'Помилка збереження файлу';
                    return;
                }
                this.originalFileContent = this.openedFile.content;
                this.fileMessage = 'Файл успішно збережено';
                await this.loadTree(this.currentPath, { selectedPath: this.openedFile.path });
                this.syncCurrentUrl(this.openedFile.path);
            } catch (e) {
                this.fileError = 'Помилка з\'єднання з сервером';
                console.error(e);
            } finally {
                this.fileSaving = false;
            }
        },

        async reloadOpenedFile() {
            if (!this.openedFile.path) return;
            await this.openFile(this.openedFile.path, { syncUrl: false });
            this.syncCurrentUrl(this.openedFile.path);
        },

        closeOpenedFile() {
            if (!this.confirmDiscardChanges()) return;
            this.resetOpenedFile();
            this.syncCurrentUrl(this.currentPath);
        },

        handleOpenedFileInput() {
            this.fileMessage = null;
        },

        isOpenedFileDirty() {
            return this.openedFile.editable && this.openedFile.content !== this.originalFileContent;
        },

        confirmDiscardChanges() {
            if (!this.isOpenedFileDirty()) return true;
            return window.confirm('Є незбережені зміни. Продовжити без збереження?');
        },

        resetOpenedFile() {
            this.filePanelVisible = false;
            this.fileLoading = false;
            this.fileSaving = false;
            this.fileError = null;
            this.fileMessage = null;
            this.openedFile = { path: '', name: '', extension: '', mimeType: '', size: null, isText: true, editable: false, content: '' };
            this.originalFileContent = '';
        },

        setSelectedItemByPath(path) {
            const normalizedPath = this.normalizePath(path);
            this.selectedItem = this.items.find(item => item.path === normalizedPath) || null;
            return this.selectedItem;
        },

        downloadFile(path) {
            if (!path) return;
            window.location.href = `{{ route('file-manager.download') }}?path=${encodeURIComponent(path)}`;
        },

        buildDirectoryDescriptor(path = '') {
            const normalizedPath = this.normalizePath(path);
            return { path: normalizedPath, name: normalizedPath ? this.basename(normalizedPath) : '/', type: 'directory', size: null, modified: null, readable: true, writable: true };
        },

        buildFileDescriptor(path = '') {
            const normalizedPath = this.normalizePath(path);
            return { path: normalizedPath, name: this.basename(normalizedPath), type: 'file', size: null, modified: null, readable: true, writable: false, extension: this.getExtensionFromPath(normalizedPath), mime_type: '' };
        },

        normalizePath(path = '') {
            return String(path || '').trim().replace(/\\/g, '/').replace(/^\/+|\/+$/g, '');
        },

        basename(path = '') {
            const normalizedPath = this.normalizePath(path);
            if (!normalizedPath) return '/';
            return normalizedPath.split('/').pop() || normalizedPath;
        },

        directoryOf(path = '') {
            const normalizedPath = this.normalizePath(path);
            if (!normalizedPath || !normalizedPath.includes('/')) return '';
            return normalizedPath.split('/').slice(0, -1).join('/');
        },

        buildTargetUrl(path = '') {
            const normalizedPath = this.normalizePath(path);
            if (!normalizedPath) return FILE_MANAGER_INDEX_URL;
            return `${FILE_MANAGER_OPEN_BASE_URL}/${normalizedPath.split('/').map(segment => encodeURIComponent(segment)).join('/')}`;
        },

        syncCurrentUrl(path = '') {
            const normalizedPath = this.normalizePath(path);
            this.activeTargetPath = normalizedPath;
            if (window.history?.replaceState) {
                window.history.replaceState({ targetPath: normalizedPath }, '', this.buildTargetUrl(normalizedPath));
            }
        },

        formatFileSize(bytes) {
            if (bytes === null || bytes === undefined) return 'N/A';
            const units = ['B', 'KB', 'MB', 'GB', 'TB'];
            let size = Number(bytes);
            let unitIndex = 0;
            while (size >= 1024 && unitIndex < units.length - 1) {
                size /= 1024;
                unitIndex++;
            }
            return `${size.toFixed(unitIndex === 0 ? 0 : 2)} ${units[unitIndex]}`;
        },

        formatDate(timestamp) {
            if (!timestamp) return 'N/A';
            return new Date(timestamp * 1000).toLocaleString('uk-UA');
        },

        getFileIcon(item) {
            const ext = item.extension ? item.extension.toLowerCase() : '';
            const iconMap = { php: 'fa-file-code text-purple-600', js: 'fa-file-code text-yellow-600', json: 'fa-file-code text-green-600', html: 'fa-file-code text-orange-600', css: 'fa-file-code text-blue-600', md: 'fa-file-lines text-gray-600', txt: 'fa-file-lines text-gray-600', env: 'fa-file-lines text-gray-600', yml: 'fa-file-lines text-cyan-600', yaml: 'fa-file-lines text-cyan-600', xml: 'fa-file-code text-pink-600', sql: 'fa-database text-indigo-600', png: 'fa-file-image text-pink-600', jpg: 'fa-file-image text-pink-600', jpeg: 'fa-file-image text-pink-600', gif: 'fa-file-image text-pink-600', svg: 'fa-file-image text-pink-600', pdf: 'fa-file-pdf text-red-600', zip: 'fa-file-archive text-gray-700', tar: 'fa-file-archive text-gray-700', gz: 'fa-file-archive text-gray-700' };
            return iconMap[ext] || 'text-gray-500';
        },

        getExtensionFromPath(path = '') {
            const normalizedPath = this.normalizePath(path);
            if (!normalizedPath || !normalizedPath.includes('.')) return '';
            return normalizedPath.split('.').pop().toLowerCase();
        },

        getCsrfToken() {
            return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        },
    };
}
</script>
@endsection

@push('styles')
<style>
    .fm-editor-textarea {
        min-height: 520px;
        width: 100%;
        resize: vertical;
        border: 1px solid #cbd5e1;
        border-radius: 0.75rem;
        background: #0f172a;
        color: #e5e7eb;
        padding: 1rem;
        font-family: Consolas, "Courier New", monospace;
        font-size: 14px;
        line-height: 1.6;
        outline: none;
    }

    .fm-editor-textarea:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.18);
    }

    .fm-viewer-pre {
        min-height: 320px;
        overflow: auto;
        border: 1px solid #cbd5e1;
        border-radius: 0.75rem;
        background: #0f172a;
        color: #e5e7eb;
        padding: 1rem;
        font-family: Consolas, "Courier New", monospace;
        font-size: 14px;
        line-height: 1.6;
        white-space: pre-wrap;
        word-break: break-word;
    }
</style>
@endpush
