@extends('layouts.app')

@section('title', 'Файловий менеджер')

@section('content')
<div
    class="container mx-auto px-4"
    x-data="fileManager(@js($initialPath ?? ''), @js($initialSelection ?? ''))"
    x-init="init()"
>
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Файловий менеджер</h1>
        <p class="text-gray-600">Перегляд структури файлів та папок проекту</p>
        <p class="text-sm text-gray-500 mt-1">Базова директорія: <code class="bg-gray-100 px-2 py-1 rounded">{{ $basePath }}</code></p>
    </div>

    <!-- Navigation Bar -->
    <div class="bg-white rounded-lg shadow mb-4 p-4">
        <div class="flex items-center gap-2 text-sm">
            <button 
                @click="navigateToRoot()" 
                class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 transition"
                title="Кореневий каталог"
            >
                <i class="fas fa-home"></i>
            </button>
            <button 
                @click="goBack()" 
                :disabled="currentPath === ''"
                class="px-3 py-1 bg-gray-500 text-white rounded hover:bg-gray-600 transition disabled:opacity-50 disabled:cursor-not-allowed"
                title="Назад"
            >
                <i class="fas fa-arrow-left"></i>
            </button>
            <div class="flex-1 bg-gray-100 px-3 py-2 rounded">
                <span class="text-gray-600">Поточний шлях:</span>
                <span class="font-mono ml-2" x-text="currentPath || '/'"></span>
            </div>
            <button 
                @click="loadTree(currentPath)" 
                class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600 transition"
                title="Оновити"
            >
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- File Tree Panel -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow">
                <div class="p-4 border-b">
                    <h2 class="text-xl font-semibold text-gray-800">
                        <i class="fas fa-folder-tree mr-2"></i>Структура файлів
                    </h2>
                </div>
                <div class="p-4">
                    <!-- Loading -->
                    <div x-show="loading" class="text-center py-8">
                        <i class="fas fa-spinner fa-spin text-4xl text-blue-500"></i>
                        <p class="text-gray-600 mt-2">Завантаження...</p>
                    </div>

                    <!-- Error -->
                    <div x-show="error" x-cloak class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <span x-text="error"></span>
                    </div>

                    <!-- File Tree -->
                    <div x-show="!loading && !error" x-cloak class="space-y-1">
                        <template x-if="items.length === 0">
                            <p class="text-gray-500 text-center py-8">Папка порожня або немає доступу</p>
                        </template>
                        
                        <template x-for="item in items" :key="item.path">
                            <div 
                                class="flex items-center gap-2 p-2 rounded hover:bg-gray-50 cursor-pointer transition"
                                :class="{ 'bg-blue-50': selectedItem && selectedItem.path === item.path }"
                                @click="selectItem(item)"
                                @dblclick="if (item.type === 'directory') navigateToPath(item.path)"
                            >
                                <!-- Icon -->
                                <div class="w-8 text-center">
                                    <template x-if="item.type === 'directory'">
                                        <i class="fas fa-folder text-yellow-500 text-xl"></i>
                                    </template>
                                    <template x-if="item.type === 'file'">
                                        <i class="fas fa-file text-gray-500" :class="getFileIcon(item)"></i>
                                    </template>
                                </div>
                                
                                <!-- Name -->
                                <div class="flex-1">
                                    <span
                                        class="font-medium"
                                        x-text="item.name"
                                        @click.stop="if (item.type === 'directory') navigateToPath(item.path)"
                                        :class="{ 'cursor-pointer text-blue-700 hover:underline': item.type === 'directory' }"
                                    ></span>
                                </div>

                                <!-- Size -->
                                <div class="text-sm text-gray-500 w-24 text-right">
                                    <span x-text="item.type === 'file' ? formatFileSize(item.size) : ''"></span>
                                </div>

                                <!-- Actions -->
                                <div class="flex gap-1">
                                    <template x-if="item.type === 'directory'">
                                        <button
                                            @click.stop="navigateToPath(item.path)"
                                            class="p-1 text-blue-600 hover:bg-blue-100 rounded"
                                            title="Відкрити"
                                        >
                                            <i class="fas fa-folder-open"></i>
                                        </button>
                                    </template>
                                    <template x-if="item.type === 'file'">
                                        <button
                                            @click.stop="previewFile(item.path)"
                                            class="p-1 text-green-600 hover:bg-green-100 rounded"
                                            title="Переглянути"
                                        >
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </template>
                                    <template x-if="item.type === 'file' && item.writable">
                                        <button
                                            @click.stop="editFile(item.path)"
                                            class="p-1 text-orange-600 hover:bg-orange-100 rounded"
                                            title="Редагувати"
                                        >
                                            <i class="fas fa-pen-to-square"></i>
                                        </button>
                                    </template>
                                    <template x-if="item.type === 'file'">
                                        <button
                                            @click.stop="downloadFile(item.path)"
                                            class="p-1 text-purple-600 hover:bg-purple-100 rounded"
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
        </div>

        <!-- Info Panel -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow sticky top-4">
                <div class="p-4 border-b">
                    <h2 class="text-xl font-semibold text-gray-800">
                        <i class="fas fa-info-circle mr-2"></i>Інформація
                    </h2>
                </div>
                <div class="p-4">
                    <template x-if="!selectedItem">
                        <p class="text-gray-500 text-center py-8">Оберіть файл або папку</p>
                    </template>
                    
                    <template x-if="selectedItem">
                        <div class="space-y-3">
                            <div>
                                <label class="text-sm font-semibold text-gray-600">Назва:</label>
                                <p class="text-gray-800 break-all" x-text="selectedItem.name"></p>
                            </div>
                            <div>
                                <label class="text-sm font-semibold text-gray-600">Тип:</label>
                                <p class="text-gray-800">
                                    <span x-text="selectedItem.type === 'directory' ? 'Директорія' : 'Файл'"></span>
                                </p>
                            </div>
                            <div>
                                <label class="text-sm font-semibold text-gray-600">Шлях:</label>
                                <p class="text-gray-800 break-all font-mono text-sm" x-text="selectedItem.path"></p>
                            </div>
                            <template x-if="selectedItem.type === 'file'">
                                <div>
                                    <label class="text-sm font-semibold text-gray-600">Розмір:</label>
                                    <p class="text-gray-800" x-text="formatFileSize(selectedItem.size)"></p>
                                </div>
                            </template>
                            <template x-if="selectedItem.type === 'file' && selectedItem.extension">
                                <div>
                                    <label class="text-sm font-semibold text-gray-600">Розширення:</label>
                                    <p class="text-gray-800" x-text="selectedItem.extension"></p>
                                </div>
                            </template>
                            <template x-if="selectedItem.mime_type">
                                <div>
                                    <label class="text-sm font-semibold text-gray-600">MIME тип:</label>
                                    <p class="text-gray-800 text-sm break-all" x-text="selectedItem.mime_type"></p>
                                </div>
                            </template>
                            <div>
                                <label class="text-sm font-semibold text-gray-600">Змінено:</label>
                                <p class="text-gray-800" x-text="formatDate(selectedItem.modified)"></p>
                            </div>
                            <div>
                                <label class="text-sm font-semibold text-gray-600">Права доступу:</label>
                                <div class="flex gap-2">
                                    <span class="px-2 py-1 text-xs rounded" :class="selectedItem.readable ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                        <i class="fas fa-eye mr-1"></i>Читання
                                    </span>
                                    <span class="px-2 py-1 text-xs rounded" :class="selectedItem.writable ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                        <i class="fas fa-pen mr-1"></i>Запис
                                    </span>
                                </div>
                            </div>
                            <template x-if="selectedItem.type === 'file' && selectedItem.writable">
                                <div class="pt-2">
                                    <button
                                        @click="editFile(selectedItem.path)"
                                        class="w-full px-3 py-2 bg-orange-500 text-white rounded hover:bg-orange-600 transition"
                                    >
                                        <i class="fas fa-pen-to-square mr-2"></i>Редагувати файл
                                    </button>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Modal -->
    <div 
        x-show="showPreview" 
        x-cloak
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
        @click.self="showPreview = false"
    >
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] flex flex-col">
            <div class="p-4 border-b flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-file-alt mr-2"></i>
                    <span x-text="previewData.name"></span>
                </h3>
                <button 
                    @click="showPreview = false"
                    class="text-gray-500 hover:text-gray-700"
                >
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="p-4 overflow-auto flex-1">
                <template x-if="previewLoading">
                    <div class="text-center py-8">
                        <i class="fas fa-spinner fa-spin text-4xl text-blue-500"></i>
                        <p class="text-gray-600 mt-2">Завантаження...</p>
                    </div>
                </template>
                
                <template x-if="!previewLoading && previewError">
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <span x-text="previewError"></span>
                    </div>
                </template>
                
                <template x-if="!previewLoading && !previewError && previewData.is_text">
                    <pre class="bg-gray-50 p-4 rounded overflow-x-auto text-sm"><code x-ref="previewCode" :class="previewLanguage" x-text="previewData.content"></code></pre>
                </template>
                
                <template x-if="!previewLoading && !previewError && !previewData.is_text">
                    <div class="text-center py-8 text-gray-600">
                        <i class="fas fa-file text-4xl mb-4"></i>
                        <p>Попередній перегляд недоступний для цього типу файлу</p>
                        <p class="text-sm mt-2">MIME тип: <code x-text="previewData.mime_type"></code></p>
                    </div>
                </template>
            </div>
            <div class="p-4 border-t bg-gray-50 flex justify-end gap-2">
                <button 
                    @click="downloadFile(previewData.path)"
                    class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition"
                >
                    <i class="fas fa-download mr-2"></i>Завантажити
                </button>
                <button 
                    @click="showPreview = false"
                    class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition"
                >
                    Закрити
                </button>
            </div>
        </div>
    </div>

    <!-- Editor Modal -->
    <div
        x-show="showEditor"
        x-cloak
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
        @click.self="closeEditor()"
    >
        <div class="bg-white rounded-lg shadow-xl max-w-5xl w-full max-h-[95vh] flex flex-col">
            <div class="p-4 border-b flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-pen-to-square mr-2"></i>
                    <span x-text="editorData.name || 'Редагування файлу'"></span>
                </h3>
                <button
                    @click="closeEditor()"
                    class="text-gray-500 hover:text-gray-700"
                >
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="p-4 flex-1 flex flex-col overflow-hidden max-h-[80vh]">
                <template x-if="editorLoading">
                    <div class="text-center py-8">
                        <i class="fas fa-spinner fa-spin text-4xl text-blue-500"></i>
                        <p class="text-gray-600 mt-2">Завантаження файлу...</p>
                    </div>
                </template>

                <template x-if="!editorLoading && editorError">
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <span x-text="editorError"></span>
                    </div>
                </template>

                <template x-if="!editorLoading && !editorError">
                    <div class="flex-1 flex flex-col overflow-hidden">
                        <div class="text-sm text-gray-500 mb-2 flex flex-wrap gap-4">
                            <span>Шлях: <code class="font-mono" x-text="editorData.path"></code></span>
                            <span>Розширення: <code x-text="editorData.extension || '—'"></code></span>
                            <span class="text-green-600" x-show="editorMessage" x-text="editorMessage"></span>
                        </div>
                        <div x-show="editorFallback" class="text-sm text-amber-600 mb-2">
                            Використовується легкий офлайн-редактор з базовою підсвіткою замість CodeMirror.
                        </div>
                        <div class="flex-1 overflow-hidden relative">
                            <textarea
                                x-ref="editorTextarea"
                                x-model="editorContent"
                                :class="editorFallback ? 'hidden' : 'opacity-0 absolute inset-0 h-0 w-0'"
                            ></textarea>
                            <div
                                x-show="editorFallback"
                                x-ref="liteEditor"
                                class="fm-lite-editor max-h-[70vh] min-h-[300px]"
                                contenteditable="true"
                                @input="handleLiteInput"
                            ></div>
                        </div>
                        <div x-show="!editorInstance && !editorFallback" class="text-center text-gray-500 py-4">
                            <p>Ініціалізація редактора...</p>
                        </div>
                    </div>
                </template>
            </div>
            <div class="p-4 border-t bg-gray-50 flex justify-end gap-2">
                <button
                    @click="saveFile()"
                    :disabled="editorSaving || editorLoading || !!editorError"
                    class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 transition disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <i class="fas fa-save mr-2"></i>
                    <span x-text="editorSaving ? 'Збереження...' : 'Зберегти'"></span>
                </button>
                <button
                    @click="closeEditor()"
                    class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition"
                >
                    Скасувати
                </button>
            </div>
        </div>
    </div>
</div>

@php
    $codeMirrorSources = array(
        route('file-manager.asset', ['path' => 'codemirror/codemirror.min.js']),
        route('file-manager.asset', ['path' => 'codemirror/mode/javascript/javascript.min.js']),
        route('file-manager.asset', ['path' => 'codemirror/mode/clike/clike.min.js']),
        route('file-manager.asset', ['path' => 'codemirror/mode/php/php.min.js']),
        route('file-manager.asset', ['path' => 'codemirror/mode/xml/xml.min.js']),
        route('file-manager.asset', ['path' => 'codemirror/mode/css/css.min.js']),
        route('file-manager.asset', ['path' => 'codemirror/mode/htmlmixed/htmlmixed.min.js']),
        route('file-manager.asset', ['path' => 'codemirror/mode/markdown/markdown.min.js']),
        route('file-manager.asset', ['path' => 'codemirror/mode/sql/sql.min.js']),
        route('file-manager.asset', ['path' => 'codemirror/mode/shell/shell.min.js']),
    );

    $highlightStyle = route('file-manager.asset', ['path' => 'highlightjs/github-dark.min.css']);
    $highlightScript = route('file-manager.asset', ['path' => 'highlightjs/highlight.min.js']);
@endphp

<script>
const FILE_MANAGER_CODEMIRROR_SOURCES = {!! json_encode($codeMirrorSources) !!};
const FILE_MANAGER_HIGHLIGHT_STYLE = {!! json_encode($highlightStyle) !!};
const FILE_MANAGER_HIGHLIGHT_SCRIPT = {!! json_encode($highlightScript) !!};

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

function fileManager(initialPath = '', initialSelection = '') {
    return {
        initialPath,
        initialSelection,
        currentPath: '',
        items: [],
        selectedItem: null,
        loading: false,
        error: null,
        showPreview: false,
        previewData: {},
        previewLoading: false,
        previewError: null,
        previewLanguage: 'language-plaintext',
        previewNeedsHighlight: false,
        previewHighlightAttempts: 0,
        maxPreviewHighlightAttempts: 30,
        showEditor: false,
        editorData: {},
        editorLoading: false,
        editorError: null,
        editorMessage: null,
        editorContent: '',
        editorSaving: false,
        editorInstance: null,
        editorNeedsMount: false,
        editorFallback: false,
        editorMountAttempts: 0,
        maxEditorMountAttempts: 50,
        editorAssetsReady: !!window.CodeMirror,
        editorAssetsPromise: null,
        editorScriptSources: FILE_MANAGER_CODEMIRROR_SOURCES,
        pathHistory: [],
        pendingSelection: null,

        init() {
            this.pendingSelection = this.initialSelection || null;
            this.loadTree(this.initialPath || '');
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
                    if (this.pendingSelection) {
                        const match = this.items.find(item => item.path === this.pendingSelection);
                        if (match) {
                            this.selectedItem = match;
                        }
                        this.pendingSelection = null;
                    }
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

        navigateToPath(path) {
            this.pathHistory.push(this.currentPath);
            this.loadTree(path);
            this.selectedItem = null;
            this.pendingSelection = null;
        },

        navigateToRoot() {
            this.pathHistory = [];
            this.loadTree('');
            this.selectedItem = null;
            this.pendingSelection = null;
        },

        goBack() {
            if (this.pathHistory.length > 0) {
                const previousPath = this.pathHistory.pop();
                this.loadTree(previousPath);
                this.selectedItem = null;
                this.pendingSelection = null;
            } else if (this.currentPath !== '') {
                // Go to parent directory
                const parts = this.currentPath.split('/');
                parts.pop();
                this.loadTree(parts.join('/'));
                this.selectedItem = null;
                this.pendingSelection = null;
            }
        },

        async previewFile(path) {
            this.showPreview = true;
            this.previewLoading = true;
            this.previewError = null;
            this.previewData = {};
            this.previewNeedsHighlight = false;
            this.previewHighlightAttempts = 0;

            try {
                const response = await fetch(`{{ route('file-manager.preview') }}?path=${encodeURIComponent(path)}`);
                const data = await response.json();

                if (data.success) {
                    this.previewData = data.content;
                    const extension = this.getExtensionFromPath(this.previewData.path || path);
                    this.previewLanguage = this.getHighlightClass(extension);
                    this.previewNeedsHighlight = !!this.previewData.is_text;
                } else {
                    this.previewError = data.error || 'Помилка завантаження';
                }
            } catch (e) {
                this.previewError = 'Помилка з\'єднання з сервером';
                console.error(e);
            } finally {
                this.previewLoading = false;
                if (this.previewNeedsHighlight) {
                    this.$nextTick(() => this.tryHighlightPreview());
                }
            }
        },

        tryHighlightPreview() {
            if (! this.previewNeedsHighlight) {
                return;
            }

            if (window.hljs && this.$refs.previewCode) {
                window.hljs.highlightElement(this.$refs.previewCode);
                this.previewNeedsHighlight = false;
                return;
            }

            // Apply the lightweight highlighter immediately so the user always sees syntax colors
            // even if Highlight.js never loads (CSP, offline, or CDN outage).
            if (this.previewHighlightAttempts === 0) {
                this.applyPreviewFallbackHighlight();
            }

            if (this.previewHighlightAttempts >= this.maxPreviewHighlightAttempts) {
                this.previewNeedsHighlight = false;
                return;
            }

            this.previewHighlightAttempts++;
            setTimeout(() => this.tryHighlightPreview(), 200);
        },

        async editFile(path) {
            this.showEditor = true;
            this.editorLoading = true;
            this.editorError = null;
            this.editorMessage = null;
            this.editorContent = '';
            this.editorData = {};
            this.destroyEditor();
            this.editorNeedsMount = false;
            this.editorFallback = false;
            this.editorMountAttempts = 0;

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
                    this.editorNeedsMount = true;
                } catch (assetError) {
                    console.error(assetError);
                    this.editorFallback = true;
                    this.editorMessage = 'CodeMirror недоступний, вмикається офлайн-редактор';
                    this.editorNeedsMount = false;
                    this.$nextTick(() => this.mountLiteEditor());
                }
            } catch (e) {
                this.editorError = 'Помилка з\'єднання з сервером';
                console.error(e);
            } finally {
                this.editorLoading = false;
                if (this.editorNeedsMount && !this.editorError) {
                    this.$nextTick(() => this.mountEditor());
                } else if (this.editorFallback) {
                    this.$nextTick(() => this.mountLiteEditor());
                }
            }
        },

        applyPreviewFallbackHighlight() {
            if (!this.$refs.previewCode || !this.previewData?.content) {
                return;
            }

            this.$refs.previewCode.innerHTML = highlightWithFallback(
                this.previewData.content,
                this.getExtensionFromPath(this.previewData.path || ''),
            );
        },

        mountEditor() {
            if (this.editorFallback) {
                this.editorNeedsMount = false;
                return;
            }

            if (!this.editorNeedsMount) {
                return;
            }

            if (this.editorMountAttempts >= this.maxEditorMountAttempts) {
                this.editorMessage = 'Не вдалося ініціалізувати CodeMirror, використовується простий редактор';
                this.editorFallback = true;
                this.editorNeedsMount = false;
                return;
            }

            this.editorMountAttempts++;

            if (!window.CodeMirror || !this.$refs.editorTextarea) {
                // Re-attempt initialization shortly if dependencies are not ready yet
                setTimeout(() => this.mountEditor(), 100);
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
                const editorHeight = Math.min(Math.max(window.innerHeight * 0.7, 320), 900);
                this.editorInstance.setSize('100%', editorHeight + 'px');
                this.editorInstance.setValue(this.editorContent);
                this.editorInstance.focus();
                this.editorNeedsMount = false;
                this.editorMessage = null;
            } catch (initError) {
                console.error(initError);
                this.editorMessage = 'Не вдалося ініціалізувати CodeMirror, вмикається офлайн-редактор';
                this.editorFallback = true;
                this.editorNeedsMount = false;
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
                script.defer = true;
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
            this.editorNeedsMount = false;
        },

        closeEditor() {
            this.showEditor = false;
            this.editorMessage = null;
            this.editorError = null;
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
                this.loadTree(this.currentPath);
            } catch (e) {
                this.editorError = 'Помилка з\'єднання з сервером';
                console.error(e);
            } finally {
                this.editorSaving = false;
            }
        },

        handleLiteInput(event) {
            this.editorContent = event.target.innerText;
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

        downloadFile(path) {
            window.location.href = `{{ route('file-manager.download') }}?path=${encodeURIComponent(path)}`;
        },

        formatFileSize(bytes) {
            if (!bytes) return 'N/A';
            const units = ['B', 'KB', 'MB', 'GB', 'TB'];
            let size = bytes;
            let unitIndex = 0;
            
            while (size > 1024 && unitIndex < units.length - 1) {
                size /= 1024;
                unitIndex++;
            }
            
            return `${size.toFixed(2)} ${units[unitIndex]}`;
        },

        formatDate(timestamp) {
            if (!timestamp) return 'N/A';
            const date = new Date(timestamp * 1000);
            return date.toLocaleString('uk-UA');
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
                'pdf': 'fa-file-pdf text-red-600',
                'jpg': 'fa-file-image text-pink-600',
                'jpeg': 'fa-file-image text-pink-600',
                'png': 'fa-file-image text-pink-600',
                'gif': 'fa-file-image text-pink-600',
                'svg': 'fa-file-image text-pink-600',
                'zip': 'fa-file-archive text-gray-700',
                'tar': 'fa-file-archive text-gray-700',
                'gz': 'fa-file-archive text-gray-700',
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

        getHighlightClass(extension) {
            const map = {
                'php': 'language-php',
                'js': 'language-javascript',
                'ts': 'language-typescript',
                'json': 'language-json',
                'html': 'language-xml',
                'vue': 'language-xml',
                'css': 'language-css',
                'scss': 'language-css',
                'md': 'language-markdown',
                'sh': 'language-bash',
                'xml': 'language-xml',
            };

            return map[extension] || 'language-plaintext';
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
        background: #0f172a;
        color: #e5e7eb;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 12px;
        overflow: auto;
        white-space: pre;
        outline: none;
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
</style>
@endpush
@endsection

@push('head-scripts')
<link rel="stylesheet" href="{{ route('file-manager.asset', ['path' => 'highlightjs/github-dark.min.css']) }}" />
<link rel="stylesheet" href="{{ route('file-manager.asset', ['path' => 'codemirror/codemirror.min.css']) }}" />
<script defer src="{{ route('file-manager.asset', ['path' => 'highlightjs/highlight.min.js']) }}"></script>
<script defer src="{{ route('file-manager.asset', ['path' => 'alpinejs/alpine.min.js']) }}"></script>
@endpush
