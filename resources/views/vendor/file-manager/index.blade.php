@extends('layouts.app')

@section('title', 'Файловий менеджер')

@section('content')
<div class="container mx-auto px-4" x-data="fileManager()">
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
                                    <span class="font-medium" x-text="item.name"></span>
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
                    <pre class="bg-gray-50 p-4 rounded overflow-x-auto text-sm"><code x-text="previewData.content"></code></pre>
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
</div>

<script>
function fileManager() {
    return {
        currentPath: '',
        items: [],
        selectedItem: null,
        loading: false,
        error: null,
        showPreview: false,
        previewData: {},
        previewLoading: false,
        previewError: null,
        pathHistory: [],

        init() {
            this.loadTree('');
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
        },

        navigateToRoot() {
            this.pathHistory = [];
            this.loadTree('');
            this.selectedItem = null;
        },

        goBack() {
            if (this.pathHistory.length > 0) {
                const previousPath = this.pathHistory.pop();
                this.loadTree(previousPath);
                this.selectedItem = null;
            } else if (this.currentPath !== '') {
                // Go to parent directory
                const parts = this.currentPath.split('/');
                parts.pop();
                this.loadTree(parts.join('/'));
                this.selectedItem = null;
            }
        },

        async previewFile(path) {
            this.showPreview = true;
            this.previewLoading = true;
            this.previewError = null;
            this.previewData = {};
            
            try {
                const response = await fetch(`{{ route('file-manager.preview') }}?path=${encodeURIComponent(path)}`);
                const data = await response.json();
                
                if (data.success) {
                    this.previewData = data.content;
                } else {
                    this.previewError = data.error || 'Помилка завантаження';
                }
            } catch (e) {
                this.previewError = 'Помилка з\'єднання з сервером';
                console.error(e);
            } finally {
                this.previewLoading = false;
            }
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
        }
    };
}
</script>
@endsection

@push('head-scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush
