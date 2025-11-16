@extends('layouts.app')

@section('title', 'Файловий менеджер - IDE режим')

@section('content')
<div
    class="container mx-auto px-4"
    x-data="fileManagerIDE(@js($initialPath ?? ''), @js($initialSelection ?? ''))"
    x-init="init()"
>
    <div class="mb-6">
        <div class="flex items-center justify-between mb-2">
            <h1 class="text-3xl font-bold text-gray-800">Файловий менеджер - IDE режим</h1>
            <div class="inline-flex rounded-lg border border-gray-300 bg-white p-1 shadow-sm">
                <a 
                    href="{{ route('file-manager.index') }}" 
                    class="px-4 py-2 text-gray-700 rounded-md font-medium hover:bg-gray-100 transition"
                >
                    <i class="fas fa-list mr-2"></i>Файловий менеджер
                </a>
                <button 
                    class="px-4 py-2 bg-blue-600 text-white rounded-md font-medium cursor-default"
                >
                    <i class="fas fa-code mr-2"></i>IDE режим
                </button>
            </div>
        </div>
        <p class="text-gray-600">Редагування файлів у стилі IDE</p>
        <p class="text-sm text-gray-500 mt-1">Базова директорія: <code class="bg-gray-100 px-2 py-1 rounded">{{ $basePath }}</code></p>
    </div>

    <!-- Main IDE Layout -->
    <div class="bg-white rounded-lg shadow overflow-hidden" style="height: calc(100vh - 250px); min-height: 500px;">
        <div class="flex h-full">
            <!-- Left Sidebar - File Tree -->
            <div class="w-64 border-r border-gray-200 flex flex-col bg-gray-50">
                <!-- Navigation Controls -->
                <div class="p-3 border-b border-gray-200 bg-white">
                    <div class="flex items-center gap-1 mb-2">
                        <button 
                            @click="navigateToRoot()" 
                            class="p-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition flex-shrink-0"
                            title="Кореневий каталог"
                        >
                            <i class="fas fa-home"></i>
                        </button>
                        <button 
                            @click="goBack()" 
                            :disabled="!canGoBack"
                            class="p-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition disabled:opacity-50 disabled:cursor-not-allowed flex-shrink-0"
                            title="Назад"
                        >
                            <i class="fas fa-arrow-left"></i>
                        </button>
                        <button 
                            @click="loadTree(currentPath)" 
                            class="p-2 bg-green-500 text-white rounded hover:bg-green-600 transition flex-shrink-0"
                            title="Оновити"
                        >
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                    <div class="text-xs text-gray-600 truncate" :title="currentPath || '/'">
                        <i class="fas fa-folder mr-1"></i>
                        <span x-text="currentPath || '/'"></span>
                    </div>
                </div>

                <!-- File Tree -->
                <div class="flex-1 overflow-y-auto p-2">
                    <!-- Loading -->
                    <div x-show="loading" class="text-center py-8">
                        <i class="fas fa-spinner fa-spin text-2xl text-blue-500"></i>
                        <p class="text-gray-600 text-sm mt-2">Завантаження...</p>
                    </div>

                    <!-- Error -->
                    <div x-show="error" x-cloak class="bg-red-100 border border-red-400 text-red-700 px-3 py-2 rounded text-sm">
                        <i class="fas fa-exclamation-circle mr-1"></i>
                        <span x-text="error"></span>
                    </div>

                    <!-- Tree Items -->
                    <div x-show="!loading && !error" x-cloak class="space-y-1">
                        <template x-if="items.length === 0">
                            <p class="text-gray-500 text-center py-8 text-sm">Папка порожня</p>
                        </template>
                        
                        <template x-for="item in items" :key="item.path">
                            <div 
                                class="flex items-center gap-2 px-2 py-1.5 rounded cursor-pointer transition text-sm"
                                :class="{ 
                                    'bg-blue-100 text-blue-800': selectedItem && selectedItem.path === item.path,
                                    'hover:bg-gray-100': !selectedItem || selectedItem.path !== item.path
                                }"
                                @click="selectItem(item)"
                                @dblclick="handleDoubleClick(item)"
                            >
                                <!-- Icon -->
                                <div class="w-5 text-center flex-shrink-0">
                                    <template x-if="item.type === 'directory'">
                                        <i class="fas fa-folder text-yellow-500"></i>
                                    </template>
                                    <template x-if="item.type === 'file'">
                                        <i class="fas fa-file text-gray-500" :class="getFileIcon(item)"></i>
                                    </template>
                                </div>
                                
                                <!-- Name -->
                                <div class="flex-1 truncate" :title="item.name">
                                    <span x-text="item.name"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Right Panel - Editor -->
            <div class="flex-1 flex flex-col bg-white">
                <!-- Editor Header -->
                <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                    <template x-if="!editorData.path">
                        <div class="text-gray-500 text-sm">
                            <i class="fas fa-info-circle mr-2"></i>
                            Оберіть файл для редагування з дерева файлів
                        </div>
                    </template>
                    
                    <template x-if="editorData.path">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="font-semibold text-gray-800 flex items-center gap-2">
                                    <i class="fas fa-file-code"></i>
                                    <span x-text="editorData.name"></span>
                                    <span x-show="editorModified" class="text-orange-600 text-xs">(змінено)</span>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    <span x-text="editorData.path"></span>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button
                                    @click="saveFile()"
                                    :disabled="editorSaving || !editorModified"
                                    class="px-3 py-1.5 bg-green-500 text-white rounded hover:bg-green-600 transition disabled:opacity-50 disabled:cursor-not-allowed text-sm"
                                >
                                    <i class="fas fa-save mr-1"></i>
                                    <span x-text="editorSaving ? 'Збереження...' : 'Зберегти'"></span>
                                </button>
                                <button
                                    @click="closeEditor()"
                                    class="px-3 py-1.5 bg-gray-500 text-white rounded hover:bg-gray-600 transition text-sm"
                                >
                                    <i class="fas fa-times mr-1"></i>Закрити
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Editor Content -->
                <div class="flex-1 overflow-hidden relative">
                    <template x-if="!editorData.path">
                        <div class="flex items-center justify-center h-full text-gray-400">
                            <div class="text-center">
                                <i class="fas fa-folder-open text-6xl mb-4"></i>
                                <p class="text-lg">Немає відкритого файлу</p>
                                <p class="text-sm mt-2">Двічі клікніть на файл в дереві для його відкриття</p>
                            </div>
                        </div>
                    </template>

                    <template x-if="editorData.path && editorLoading">
                        <div class="flex items-center justify-center h-full">
                            <div class="text-center">
                                <i class="fas fa-spinner fa-spin text-4xl text-blue-500"></i>
                                <p class="text-gray-600 mt-2">Завантаження файлу...</p>
                            </div>
                        </div>
                    </template>

                    <template x-if="editorData.path && !editorLoading && editorError">
                        <div class="flex items-center justify-center h-full p-4">
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded max-w-lg">
                                <i class="fas fa-exclamation-circle mr-2"></i>
                                <span x-text="editorError"></span>
                            </div>
                        </div>
                    </template>

                    <template x-if="editorData.path && !editorLoading && !editorError">
                        <div class="h-full flex flex-col">
                            <div x-show="editorMessage" class="px-4 py-2 bg-green-100 border-b border-green-200 text-green-700 text-sm">
                                <i class="fas fa-check-circle mr-2"></i>
                                <span x-text="editorMessage"></span>
                            </div>
                            <div x-show="editorFallback" class="px-4 py-2 bg-amber-100 border-b border-amber-200 text-amber-700 text-sm">
                                Використовується офлайн-редактор з базовою підсвіткою
                            </div>
                            <div class="flex-1 relative overflow-hidden">
                                <textarea
                                    x-ref="editorTextarea"
                                    x-model="editorContent"
                                    @input="editorModified = true"
                                    :class="editorFallback ? 'hidden' : 'opacity-0 absolute inset-0 h-0 w-0'"
                                ></textarea>
                                <div
                                    x-show="editorFallback"
                                    x-ref="liteEditor"
                                    class="fm-lite-editor h-full"
                                    contenteditable="true"
                                    @input="handleLiteInput"
                                ></div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

@php
    $codeMirrorSources = array(
        route('file-manager.asset', ['path' => 'codemirror/codemirror.min.js']),
        route('file-manager.asset', ['path' => 'codemirror/mode/javascript/javascript.min.js']),
        route('file-manager.asset', ['path' => 'codemirror/mode/php/php.min.js']),
        route('file-manager.asset', ['path' => 'codemirror/mode/xml/xml.min.js']),
        route('file-manager.asset', ['path' => 'codemirror/mode/css/css.min.js']),
        route('file-manager.asset', ['path' => 'codemirror/mode/htmlmixed/htmlmixed.min.js']),
        route('file-manager.asset', ['path' => 'codemirror/mode/markdown/markdown.min.js']),
        route('file-manager.asset', ['path' => 'codemirror/mode/sql/sql.min.js']),
        route('file-manager.asset', ['path' => 'codemirror/mode/shell/shell.min.js']),
    );
@endphp

<script>
const FILE_MANAGER_CODEMIRROR_SOURCES = {!! json_encode($codeMirrorSources) !!};

const FILE_MANAGER_FALLBACK_KEYWORDS = {
    php: ['function', 'class', 'public', 'protected', 'private', 'return', 'if', 'else', 'elseif', 'foreach', 'for', 'while', 'switch', 'case', 'default', 'break', 'continue', 'try', 'catch', 'finally', 'new', 'use', 'namespace', 'extends', 'implements', 'static', 'self', 'parent', 'echo', 'null', 'true', 'false'],
    js: ['function', 'const', 'let', 'var', 'return', 'if', 'else', 'for', 'while', 'switch', 'case', 'default', 'break', 'continue', 'try', 'catch', 'finally', 'class', 'extends', 'new', 'import', 'from', 'export', 'null', 'true', 'false'],
    ts: ['function', 'const', 'let', 'var', 'return', 'if', 'else', 'for', 'while', 'switch', 'case', 'default', 'break', 'continue', 'try', 'catch', 'finally', 'class', 'extends', 'new', 'import', 'from', 'export', 'type', 'interface', 'implements', 'public', 'private', 'protected', 'readonly', 'null', 'true', 'false'],
    css: ['color', 'background', 'display', 'flex', 'grid', 'position', 'absolute', 'relative', 'font', 'padding', 'margin', 'border'],
    sql: ['select', 'insert', 'update', 'delete', 'from', 'where', 'join', 'left', 'right', 'inner', 'outer', 'group', 'by', 'order', 'limit', 'values', 'into', 'create', 'table', 'drop', 'alter'],
};

function escapeHtmlSafe(value) {
    return (value || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function highlightWithFallback(code, extension) {
    const text = typeof code === 'string' ? code : '';
    const patterns = [
        { regex: /\/\*[\s\S]*?\*\//g, className: 'comment' },
        { regex: /(^|[^:])\/\/.*$/gm, className: 'comment' },
        { regex: /#.*$/gm, className: 'comment' },
        { regex: /'(?:\\.|[^'\\])*'|"(?:\\.|[^"\\])*"/g, className: 'string' },
        { regex: /\b\d+(?:\.\d+)?\b/g, className: 'number' },
    ];

    const keywordList = FILE_MANAGER_FALLBACK_KEYWORDS[extension] || [];
    if (keywordList.length) {
        const keywordRegex = new RegExp(`\\b(${keywordList.join('|')})\\b`, 'gi');
        patterns.push({ regex: keywordRegex, className: 'keyword' });
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

function fileManagerIDE(initialPath = '', initialSelection = '') {
    return {
        initialPath,
        initialSelection,
        currentPath: '',
        items: [],
        selectedItem: null,
        loading: false,
        error: null,
        pathHistory: [],
        canGoBack: false,
        
        // Editor state
        editorData: {},
        editorLoading: false,
        editorError: null,
        editorMessage: null,
        editorContent: '',
        editorSaving: false,
        editorInstance: null,
        editorModified: false,
        editorFallback: false,
        editorAssetsReady: !!window.CodeMirror,
        editorAssetsPromise: null,
        editorScriptSources: FILE_MANAGER_CODEMIRROR_SOURCES,

        init() {
            this.loadTree(this.initialPath || '');
            if (this.initialSelection) {
                this.openFile(this.initialSelection);
            }
        },

        async loadTree(path) {
            this.loading = true;
            this.error = null;
            
            try {
                const response = await fetch(`{{ route('file-manager.tree') }}?path=${encodeURIComponent(path)}`);
                const data = await response.json();
                
                if (data.success) {
                    this.items = data.items;
                    this.currentPath = data.path;
                    this.updateCanGoBack();
                } else {
                    this.error = data.error || 'Помилка завантаження';
                }
            } catch (e) {
                this.error = 'Помилка з\'єднання з сервером';
                console.error(e);
            } finally {
                this.loading = false;
            }
        },

        selectItem(item) {
            this.selectedItem = item;
        },

        handleDoubleClick(item) {
            if (item.type === 'directory') {
                this.navigateToPath(item.path);
            } else if (item.type === 'file') {
                this.openFile(item.path);
            }
        },

        navigateToPath(path) {
            this.pathHistory.push(this.currentPath);
            this.loadTree(path);
            this.selectedItem = null;
            this.updateCanGoBack();
        },

        navigateToRoot() {
            this.pathHistory = [];
            this.loadTree('');
            this.selectedItem = null;
            this.updateCanGoBack();
        },

        goBack() {
            if (this.pathHistory.length > 0) {
                const previousPath = this.pathHistory.pop();
                this.loadTree(previousPath);
                this.selectedItem = null;
            } else if (this.currentPath !== '') {
                const parts = this.currentPath.split('/');
                parts.pop();
                this.loadTree(parts.join('/'));
                this.selectedItem = null;
            }
            this.updateCanGoBack();
        },

        updateCanGoBack() {
            this.canGoBack = this.pathHistory.length > 0 || this.currentPath !== '';
        },

        async openFile(path) {
            this.editorLoading = true;
            this.editorError = null;
            this.editorMessage = null;
            this.editorContent = '';
            this.editorModified = false;
            this.destroyEditor();
            this.editorFallback = false;

            try {
                const response = await fetch(`{{ route('file-manager.content') }}?path=${encodeURIComponent(path)}`);
                const data = await response.json();

                if (!data.success) {
                    this.editorError = data.error || 'Не вдалося завантажити файл';
                    return;
                }

                if (!data.content.is_text) {
                    this.editorError = 'Цей файл не можна редагувати онлайн';
                    return;
                }

                this.editorData = {
                    path: data.content.path,
                    name: data.content.name,
                    extension: this.getExtensionFromPath(data.content.path),
                };
                this.editorContent = data.content.content || '';

                try {
                    await this.ensureEditorAssets();
                    this.$nextTick(() => this.mountEditor());
                } catch (assetError) {
                    console.error(assetError);
                    this.editorFallback = true;
                    this.$nextTick(() => this.mountLiteEditor());
                }
            } catch (e) {
                this.editorError = 'Помилка з\'єднання з сервером';
                console.error(e);
            } finally {
                this.editorLoading = false;
            }
        },

        mountEditor() {
            if (this.editorFallback || !window.CodeMirror || !this.$refs.editorTextarea) {
                return;
            }

            try {
                this.editorInstance = CodeMirror.fromTextArea(this.$refs.editorTextarea, {
                    lineNumbers: true,
                    tabSize: 4,
                    indentUnit: 4,
                    mode: this.getEditorMode(this.editorData.extension),
                    lineWrapping: true,
                });
                this.editorInstance.setSize('100%', '100%');
                this.editorInstance.setValue(this.editorContent);
                this.editorInstance.on('change', () => {
                    this.editorModified = true;
                });
                this.editorInstance.focus();
            } catch (initError) {
                console.error(initError);
                this.editorFallback = true;
                this.$nextTick(() => this.mountLiteEditor());
            }
        },

        async ensureEditorAssets() {
            if (this.editorAssetsReady && window.CodeMirror) {
                return;
            }

            if (this.editorAssetsPromise) {
                return this.editorAssetsPromise;
            }

            const scripts = this.editorScriptSources || [];
            if (scripts.length === 0) {
                this.editorAssetsReady = true;
                return;
            }

            const loadChain = scripts.reduce((chain, src) => chain.then(() => this.loadScript(src)), Promise.resolve());
            const timeoutMs = 8000;
            const timeout = new Promise((_, reject) => setTimeout(
                () => reject(new Error('Перевищено час очікування завантаження редактора')),
                timeoutMs,
            ));

            this.editorAssetsPromise = Promise.race([loadChain, timeout])
                .then(() => {
                    if (!window.CodeMirror) {
                        throw new Error('CodeMirror глобальний об\'єкт недоступний після завантаження');
                    }
                    this.editorAssetsReady = true;
                })
                .catch(error => {
                    this.editorAssetsPromise = null;
                    throw error;
                });

            return this.editorAssetsPromise;
        },

        loadScript(src) {
            return new Promise((resolve, reject) => {
                const existing = document.querySelector(`script[data-fm-src="${src}"]`);
                if (existing) {
                    if (existing.dataset.loaded === 'true') {
                        resolve();
                        return;
                    }

                    existing.addEventListener('load', () => resolve(), { once: true });
                    existing.addEventListener('error', () => reject(new Error(`Не вдалося завантажити ${src}`)), { once: true });
                    return;
                }

                const script = document.createElement('script');
                script.src = src;
                script.dataset.fmSrc = src;
                script.onload = () => {
                    script.dataset.loaded = 'true';
                    resolve();
                };
                script.onerror = () => reject(new Error(`Не вдалося завантажити ${src}`));
                document.head.appendChild(script);
            });
        },

        destroyEditor() {
            if (this.editorInstance) {
                this.editorInstance.toTextArea();
                this.editorInstance = null;
            }
        },

        closeEditor() {
            if (this.editorModified) {
                if (!confirm('Є незбережені зміни. Все одно закрити?')) {
                    return;
                }
            }
            this.editorData = {};
            this.editorMessage = null;
            this.editorError = null;
            this.editorModified = false;
            this.destroyEditor();
        },

        async saveFile() {
            if (!this.editorData.path) {
                return;
            }

            this.editorSaving = true;
            this.editorMessage = null;
            const payload = {
                path: this.editorData.path,
                content: this.editorInstance ? this.editorInstance.getValue() : this.editorContent,
            };

            try {
                const response = await fetch(`{{ route('file-manager.update') }}`, {
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
                    this.editorError = data.error || 'Помилка збереження файлу';
                    return;
                }

                this.editorMessage = 'Файл збережено (' + (data.size || payload.content.length) + ' байт)';
                this.editorError = null;
                this.editorModified = false;
                this.loadTree(this.currentPath);
                
                setTimeout(() => {
                    this.editorMessage = null;
                }, 3000);
            } catch (e) {
                this.editorError = 'Помилка з\'єднання з сервером';
                console.error(e);
            } finally {
                this.editorSaving = false;
            }
        },

        handleLiteInput(event) {
            this.editorContent = event.target.innerText;
            this.editorModified = true;
            this.refreshLiteHighlight();
        },

        mountLiteEditor() {
            if (!this.$refs.liteEditor) {
                return;
            }

            this.$refs.liteEditor.innerText = this.editorContent;
            this.refreshLiteHighlight();
        },

        refreshLiteHighlight() {
            if (!this.editorFallback || !this.$refs.liteEditor) {
                return;
            }

            const caret = getCaretIndex(this.$refs.liteEditor);
            this.$refs.liteEditor.innerHTML = highlightWithFallback(this.editorContent, this.editorData.extension);
            setCaretIndex(this.$refs.liteEditor, caret);
        },

        getFileIcon(item) {
            const ext = item.extension ? item.extension.toLowerCase() : '';
            const iconMap = {
                'php': 'fa-file-code text-purple-600',
                'js': 'fa-file-code text-yellow-600',
                'json': 'fa-file-code text-green-600',
                'html': 'fa-file-code text-orange-600',
                'css': 'fa-file-code text-blue-600',
                'md': 'fa-file-alt text-gray-600',
                'txt': 'fa-file-alt text-gray-600',
            };

            return iconMap[ext] || 'text-gray-500';
        },

        getCsrfToken() {
            return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        },

        getExtensionFromPath(path = '') {
            if (!path || !path.includes('.')) {
                return '';
            }

            return path.split('.').pop().toLowerCase();
        },

        getEditorMode(extension) {
            const map = {
                'php': 'application/x-httpd-php',
                'js': 'javascript',
                'ts': 'javascript',
                'json': 'application/json',
                'html': 'htmlmixed',
                'vue': 'htmlmixed',
                'css': 'css',
                'scss': 'css',
                'md': 'markdown',
                'sh': 'shell',
                'sql': 'sql',
            };

            return map[extension] || 'text/plain';
        },
    };
}
</script>

@push('styles')
<style>
    .fm-lite-editor {
        font-family: 'Fira Code', 'Source Code Pro', monospace;
        font-size: 14px;
        background: #0f172a;
        color: #e5e7eb;
        border: none;
        padding: 16px;
        overflow: auto;
        white-space: pre;
        outline: none;
        width: 100%;
        height: 100%;
    }

    .fm-token.keyword {
        color: #c084fc;
        font-weight: 600;
    }

    .fm-token.string {
        color: #34d399;
    }

    .fm-token.comment {
        color: #9ca3af;
        font-style: italic;
    }

    .fm-token.number {
        color: #fbbf24;
    }

    .CodeMirror {
        height: 100%;
        font-size: 14px;
    }
</style>
@endpush

@push('head-scripts')
<link rel="stylesheet" href="{{ route('file-manager.asset', ['path' => 'codemirror/codemirror.min.css']) }}" />
<script defer src="{{ route('file-manager.asset', ['path' => 'alpinejs/alpine.min.js']) }}"></script>
@endpush
@endsection
