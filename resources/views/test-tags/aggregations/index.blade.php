@extends('layouts.app')

@section('title', 'Агрегація тегів')

@section('content')
    <div class="py-8">
        <div class="mx-auto flex max-w-5xl flex-col gap-8">
            <header class="space-y-2">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 class="text-3xl font-semibold text-slate-800">Агрегація тегів</h1>
                        <p class="text-slate-500">Об'єднуйте схожі теги під одним головним тегом.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a
                            href="{{ route('test-tags.index') }}"
                            class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring"
                        >
                            ← Назад до тегів
                        </a>
                    </div>
                </div>
            </header>

            @if (session('status'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Tabs Navigation --}}
            <div class="border-b border-slate-200">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <a
                        href="{{ route('test-tags.aggregations.index') }}"
                        class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium {{ !$isAutoPage ? 'border-blue-500 text-blue-600' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' }}"
                    >
                        Ручне управління
                    </a>
                    <a
                        href="{{ route('test-tags.aggregations.auto-page') }}"
                        class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium {{ $isAutoPage ? 'border-blue-500 text-blue-600' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' }}"
                    >
                        Автоматична агрегація
                    </a>
                </nav>
            </div>

            {{-- Manual Tab Content --}}
            @if(!$isAutoPage)
            <div id="content-manual" class="tab-content space-y-8">

            <section class="space-y-4">
                <h2 class="text-xl font-semibold text-slate-800">Створити нову агрегацію</h2>
                <form method="POST" action="{{ route('test-tags.aggregations.store') }}" class="space-y-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    @csrf
                    
                    <div>
                        <label for="main_tag" class="block text-sm font-medium text-slate-700 mb-1">
                            Головний тег
                        </label>
                        <div class="relative">
                            <input
                                type="text"
                                id="main_tag"
                                name="main_tag"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                placeholder="Почніть вводити для пошуку..."
                                autocomplete="off"
                                required
                            >
                            <div id="main_tag_dropdown" class="hidden absolute z-10 w-full mt-1 bg-white border border-slate-300 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                                @foreach ($tagsByCategory as $category => $tags)
                                    <div class="px-3 py-2 bg-slate-100 text-xs font-semibold text-slate-600 sticky top-0">
                                        {{ $category }}
                                    </div>
                                    @foreach ($tags as $tag)
                                        <div class="tag-option px-3 py-2 cursor-pointer hover:bg-blue-50 text-sm" data-value="{{ $tag->name }}" data-category="{{ $category }}">
                                            {{ $tag->name }}
                                        </div>
                                    @endforeach
                                @endforeach
                            </div>
                        </div>
                        @error('main_tag')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="category" class="block text-sm font-medium text-slate-700 mb-1">
                            Категорія (необов'язково)
                        </label>
                        <div class="relative">
                            <input
                                type="text"
                                id="category"
                                name="category"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                placeholder="Почніть вводити для пошуку..."
                                autocomplete="off"
                            >
                            <div id="category_dropdown" class="hidden absolute z-10 w-full mt-1 bg-white border border-slate-300 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                                @foreach ($categories as $cat)
                                    <div class="category-option px-3 py-2 cursor-pointer hover:bg-blue-50 text-sm" data-value="{{ $cat }}">
                                        {{ $cat }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @error('category')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Схожі теги (по одному на рядок)
                        </label>
                        <div id="similar-tags-container" class="space-y-2">
                            <div class="flex gap-2 tag-input-group">
                                <div class="flex-1 relative">
                                    <input
                                        type="text"
                                        name="similar_tags[]"
                                        class="similar-tag-input w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                        placeholder="Почніть вводити для пошуку..."
                                        autocomplete="off"
                                        required
                                    >
                                    <div class="tag-dropdown hidden absolute z-10 w-full mt-1 bg-white border border-slate-300 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                                        @foreach ($tagsByCategory as $category => $tags)
                                            <div class="px-3 py-2 bg-slate-100 text-xs font-semibold text-slate-600 sticky top-0">
                                                {{ $category }}
                                            </div>
                                            @foreach ($tags as $tag)
                                                <div class="tag-option px-3 py-2 cursor-pointer hover:bg-blue-50 text-sm" data-value="{{ $tag->name }}" data-category="{{ $category }}">
                                                    {{ $tag->name }}
                                                </div>
                                            @endforeach
                                        @endforeach
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    onclick="removeTagInput(this)"
                                    class="rounded-lg border border-red-200 px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50"
                                >
                                    Видалити
                                </button>
                            </div>
                        </div>
                        <button
                            type="button"
                            onclick="addTagInput()"
                            class="mt-2 inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50"
                        >
                            + Додати тег
                        </button>
                        @error('similar_tags')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-3">
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-blue-700 focus:outline-none focus:ring"
                        >
                            Створити агрегацію
                        </button>
                    </div>
                </form>
            </section>

            <section class="space-y-4" id="aggregations-section">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="text-xl font-semibold text-slate-800">Існуючі агрегації</h2>
                    @if (!empty($aggregations))
                        <div class="flex-1 max-w-md">
                            <input
                                type="text"
                                id="search-aggregations"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                placeholder="Пошук по категоріям та тегам..."
                            >
                        </div>
                    @endif
                </div>
                @if (empty($aggregations))
                    <p class="text-sm text-slate-500 rounded-xl border border-slate-200 bg-white p-6">
                        Агрегації ще не створено.
                    </p>
                @else
                    <div class="space-y-4" id="aggregations-list">
                        @foreach ($aggregationsByCategory as $category => $categoryAggregations)
                            <div class="aggregation-category-block rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden" data-category="{{ strtolower($category) }}" data-tags="{{ strtolower(implode(' ', collect($categoryAggregations)->flatMap(fn($a) => array_merge([$a['main_tag']], $a['similar_tags'] ?? []))->toArray())) }}">
                                {{-- Category Header --}}
                                <div class="flex items-center justify-between px-6 py-4 bg-slate-50">
                                    <button
                                        type="button"
                                        onclick="toggleAggregationCategory('{{ $loop->index }}')"
                                        class="flex items-center gap-3 flex-1 text-left hover:opacity-80 transition-opacity"
                                    >
                                        <i id="icon-{{ $loop->index }}" class="fa-solid fa-chevron-right text-slate-400 transition-transform"></i>
                                        <div>
                                            <h3 class="text-lg font-semibold text-slate-800 category-name">{{ $category }}</h3>
                                            <p class="text-sm text-slate-500">{{ count($categoryAggregations) }} {{ count($categoryAggregations) === 1 ? 'агрегація' : 'агрегацій' }}</p>
                                        </div>
                                    </button>
                                    <div class="flex gap-2">
                                        <button
                                            type="button"
                                            onclick="editCategoryName('{{ $loop->index }}', '{{ addslashes($category) }}')"
                                            class="inline-flex items-center rounded-lg border border-slate-300 px-2 py-1 text-xs font-medium text-slate-700 hover:bg-white"
                                            title="Редагувати категорію"
                                        >
                                            <i class="fa-solid fa-edit"></i>
                                        </button>
                                        <form
                                            action="{{ route('test-tags.aggregations.destroy-category', ['category' => urlencode($category)]) }}"
                                            method="POST"
                                            data-confirm="Видалити категорію «{{ $category }}» та всі її агрегації?"
                                            class="inline"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="inline-flex items-center rounded-lg border border-red-200 px-2 py-1 text-xs font-medium text-red-600 hover:bg-red-50"
                                                title="Видалити категорію"
                                            >
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                
                                {{-- Category Content --}}
                                <div id="category-{{ $loop->index }}" class="border-t border-slate-200 hidden">
                                    <div class="p-4 space-y-4">
                                        @foreach ($categoryAggregations as $aggregation)
                                            <div 
                                                class="rounded-lg border border-slate-200 bg-white p-4 aggregation-drop-zone" 
                                                data-main-tag="{{ strtolower($aggregation['main_tag']) }}"
                                                data-main-tag-exact="{{ $aggregation['main_tag'] }}"
                                                data-category="{{ $aggregation['category'] ?? '' }}"
                                            >
                                                <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
                                                    <div class="flex-1">
                                                        <h4 class="text-base font-semibold text-slate-800 main-tag-text">
                                                            {{ $aggregation['main_tag'] }}
                                                        </h4>
                                                        <p class="text-xs text-slate-500">Головний тег</p>
                                                    </div>
                                                    <div class="flex gap-2">
                                                        <button
                                                            type="button"
                                                            onclick="editAggregation('{{ addslashes($aggregation['main_tag']) }}', {{ json_encode($aggregation['similar_tags'] ?? []) }}, '{{ addslashes($aggregation['category'] ?? '') }}')"
                                                            class="inline-flex items-center rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50"
                                                        >
                                                            <i class="fa-solid fa-edit mr-1"></i>Редагувати
                                                        </button>
                                                        <form
                                                            action="{{ route('test-tags.aggregations.destroy', ['mainTag' => $aggregation['main_tag']]) }}"
                                                            method="POST"
                                                            data-confirm="Видалити агрегацію для тегу «{{ $aggregation['main_tag'] }}»?"
                                                        >
                                                            @csrf
                                                            @method('DELETE')
                                                            <button
                                                                type="submit"
                                                                class="inline-flex items-center rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50"
                                                            >
                                                                <i class="fa-solid fa-trash mr-1"></i>Видалити
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                                <div>
                                                    <p class="text-xs font-medium text-slate-700 mb-2">Схожі теги:</p>
                                                    <div class="flex flex-wrap gap-2 similar-tags-container">
                                                        @foreach ($aggregation['similar_tags'] ?? [] as $similarTag)
                                                            <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 similar-tag-badge" data-tag="{{ strtolower($similarTag) }}">
                                                                <span class="similar-tag-text">{{ $similarTag }}</span>
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="space-y-4" id="non-aggregated-section">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="text-xl font-semibold text-slate-800">Неагреговані теги</h2>
                    @if (!$nonAggregatedTags->isEmpty())
                        <button
                            type="button"
                            id="toggle-drag-mode-btn"
                            onclick="toggleDragDropMode()"
                            class="inline-flex items-center rounded-lg border border-purple-300 bg-purple-50 px-4 py-2 text-sm font-medium text-purple-700 hover:bg-purple-100 focus:outline-none focus:ring"
                        >
                            <i class="fa-solid fa-hand-pointer mr-2"></i>Увімкнути Drag & Drop
                        </button>
                    @endif
                </div>
                @if ($nonAggregatedTags->isEmpty())
                    <p class="text-sm text-slate-500 rounded-xl border border-slate-200 bg-white p-6">
                        Всі теги вже агреговані.
                    </p>
                @else
                    <div class="space-y-4">
                        @foreach ($nonAggregatedByCategory as $category => $tags)
                            <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                                {{-- Category Header --}}
                                <div class="flex items-center justify-between px-6 py-4 bg-slate-50">
                                    <button
                                        type="button"
                                        onclick="toggleNonAggregatedCategory('{{ $loop->index }}')"
                                        class="flex items-center gap-3 flex-1 text-left hover:opacity-80 transition-opacity"
                                    >
                                        <i id="non-agg-icon-{{ $loop->index }}" class="fa-solid fa-chevron-right text-slate-400 transition-transform"></i>
                                        <div>
                                            <h3 class="text-lg font-semibold text-slate-800">{{ $category }}</h3>
                                            <p class="text-sm text-slate-500">{{ count($tags) }} {{ count($tags) === 1 ? 'тег' : 'тегів' }}</p>
                                        </div>
                                    </button>
                                </div>
                                
                                {{-- Category Content --}}
                                <div id="non-agg-category-{{ $loop->index }}" class="border-t border-slate-200 hidden">
                                    <div class="p-4">
                                        <div class="flex flex-wrap gap-2">
                                            @foreach ($tags as $tag)
                                                <span 
                                                    class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-sm font-medium text-slate-700 border border-slate-300 non-aggregated-tag"
                                                    data-tag-name="{{ $tag->name }}"
                                                    data-tag-category="{{ $tag->category }}"
                                                >
                                                    {{ $tag->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="rounded-xl border border-blue-200 bg-blue-50 p-6">
                <h3 class="text-lg font-semibold text-blue-900 mb-2">
                    <i class="fa-solid fa-circle-info mr-2"></i>Інформація про файл конфігурації
                </h3>
                <p class="text-sm text-blue-800 mb-2">
                    Агрегації зберігаються у файлі: <code class="bg-blue-100 px-2 py-1 rounded">config/tags/aggregation.json</code>
                </p>
                <p class="text-sm text-blue-800">
                    Цей файл доступний у Git і може бути керований вручну або через цей інтерфейс.
                </p>
            </section>

            <section class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-slate-800">JSON конфігурація</h2>
                    <button
                        type="button"
                        onclick="toggleJsonConfig()"
                        id="toggle-json-btn"
                        class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                    >
                        <i class="fa-solid fa-chevron-down mr-2"></i>Показати JSON
                    </button>
                </div>
                <div id="json-config-container" class="hidden">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-6 shadow-sm">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-semibold text-slate-700">config/tags/aggregation.json</h3>
                            <button
                                type="button"
                                onclick="copyJsonToClipboard()"
                                class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50"
                            >
                                <i class="fa-solid fa-copy mr-2"></i>Копіювати
                            </button>
                        </div>
                        <pre id="json-display" class="bg-slate-900 text-slate-100 p-4 rounded-lg overflow-x-auto text-xs font-mono"><code>{{ json_encode(['aggregations' => $aggregations], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                    </div>
                </div>
            </section>

            <section class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-slate-800">Імпорт JSON</h2>
                    <button
                        type="button"
                        onclick="toggleImportForm()"
                        id="toggle-import-btn"
                        class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                    >
                        <i class="fa-solid fa-chevron-down mr-2"></i>Показати форму імпорту
                    </button>
                </div>
                <div id="import-form-container" class="hidden">
                    <form method="POST" action="{{ route('test-tags.aggregations.import') }}" class="space-y-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                        @csrf
                        
                        <div>
                            <label for="json_data" class="block text-sm font-medium text-slate-700 mb-1">
                                JSON дані
                            </label>
                            <textarea
                                id="json_data"
                                name="json_data"
                                rows="10"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-mono focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                placeholder='{"aggregations": [{"main_tag": "Example", "similar_tags": ["tag1", "tag2"], "category": "Category"}]}'
                                required
                            >{{ old('json_data') }}</textarea>
                            <p class="mt-1 text-xs text-slate-500">
                                Вставте JSON з агрегованими тегами у форматі: <code class="bg-slate-100 px-1 py-0.5 rounded">{"aggregations": [...]}</code>
                            </p>
                            @error('json_data')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end gap-3">
                            <button
                                type="button"
                                onclick="toggleImportForm()"
                                class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                            >
                                Скасувати
                            </button>
                            <button
                                type="submit"
                                class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-blue-700 focus:outline-none focus:ring"
                            >
                                <i class="fa-solid fa-upload mr-2"></i>Імпортувати
                            </button>
                        </div>
                    </form>
                </div>
            </section>
            </div>
            @endif
            {{-- End Manual Tab Content --}}

            {{-- Auto-Aggregation Tab Content --}}
            @if($isAutoPage)
            <div id="content-auto" class="tab-content space-y-8">
                <section class="space-y-4">
                    <h2 class="text-xl font-semibold text-slate-800">Автоматична агрегація тегів</h2>
                    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-6">
                        {{-- Step 1: Generate Prompt --}}
                        <div id="step-generate-prompt">
                            <h3 class="text-lg font-medium text-slate-700 mb-3">Крок 1: Генерація промпту</h3>
                            <p class="text-sm text-slate-600 mb-4">
                                Спочатку згенеруйте промпт для AI, який можна скопіювати та використати в ChatGPT або Gemini.
                            </p>
                            <button
                                type="button"
                                onclick="generatePrompt()"
                                id="btn-generate-prompt"
                                class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-indigo-700 focus:outline-none focus:ring"
                            >
                                <i class="fa-solid fa-wand-magic-sparkles mr-2"></i>Згенерувати промпт
                            </button>
                        </div>

                        {{-- Step 2: Display Prompt --}}
                        <div id="step-display-prompt" class="hidden space-y-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium text-slate-700">Крок 2: Промпт для AI</h3>
                                <button
                                    type="button"
                                    onclick="copyPromptToClipboard()"
                                    class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50"
                                >
                                    <i class="fa-solid fa-copy mr-2"></i>Копіювати промпт
                                </button>
                            </div>
                            <pre id="generated-prompt" class="bg-slate-900 text-slate-100 p-4 rounded-lg overflow-x-auto text-xs font-mono whitespace-pre-wrap"></pre>
                            <p class="text-sm text-slate-600">
                                Скопіюйте цей промпт і використайте його в ChatGPT або Gemini, або продовжте автоматично через API.
                            </p>
                        </div>

                        {{-- Step 3: Continue Options --}}
                        <div id="step-continue" class="hidden space-y-4">
                            <h3 class="text-lg font-medium text-slate-700">Крок 3: Продовження</h3>
                            <p class="text-sm text-slate-600 mb-4">
                                Виберіть спосіб продовження: використати API для автоматичної генерації або вставити готовий JSON.
                            </p>
                            
                            <div class="flex flex-wrap gap-3">
                                <form method="POST" action="{{ route('test-tags.aggregations.auto') }}" class="inline">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="inline-flex items-center justify-center rounded-lg bg-purple-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-purple-700 focus:outline-none focus:ring"
                                    >
                                        <i class="fa-solid fa-wand-magic-sparkles mr-2"></i>Автогенерація (Gemini API)
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('test-tags.aggregations.auto-chatgpt') }}" class="inline">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-blue-700 focus:outline-none focus:ring"
                                    >
                                        <i class="fa-solid fa-robot mr-2"></i>Автогенерація (ChatGPT API)
                                    </button>
                                </form>
                                <button
                                    type="button"
                                    onclick="togglePasteJson()"
                                    class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring"
                                >
                                    <i class="fa-solid fa-paste mr-2"></i>Вставити готовий JSON
                                </button>
                            </div>

                            {{-- Paste JSON Section --}}
                            <div id="paste-json-section" class="hidden mt-4">
                                <form method="POST" action="{{ route('test-tags.aggregations.import') }}" class="space-y-4 rounded-xl border border-slate-200 bg-slate-50 p-6">
                                    @csrf
                                    
                                    <div>
                                        <label for="json_data_auto" class="block text-sm font-medium text-slate-700 mb-1">
                                            JSON дані від AI
                                        </label>
                                        <textarea
                                            id="json_data_auto"
                                            name="json_data"
                                            rows="10"
                                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-mono focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                            placeholder='[{"main_tag": "Present Simple", "similar_tags": ["Simple Present", "Present Tense"]}, ...]'
                                            required
                                        ></textarea>
                                        <p class="mt-1 text-xs text-slate-500">
                                            Вставте JSON відповідь від ChatGPT або Gemini (масив агрегацій). Категорії будуть автоматично додані на основі головного тегу.
                                        </p>
                                    </div>

                                    <div class="flex justify-end gap-3">
                                        <button
                                            type="button"
                                            onclick="togglePasteJson()"
                                            class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                                        >
                                            Скасувати
                                        </button>
                                        <button
                                            type="submit"
                                            class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-blue-700 focus:outline-none focus:ring"
                                        >
                                            <i class="fa-solid fa-upload mr-2"></i>Імпортувати JSON
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
            @endif
            {{-- End Auto-Aggregation Tab Content --}}
        </div>
    </div>

    <div
        id="aggregation-confirmation-modal"
        class="fixed inset-0 z-40 hidden items-center justify-center"
        role="dialog"
        aria-modal="true"
        aria-labelledby="aggregation-confirmation-title"
    >
        <div class="absolute inset-0 bg-slate-900/50" data-confirm-overlay></div>
        <div class="relative w-full max-w-sm space-y-5 rounded-xl bg-white px-6 py-5 shadow-xl">
            <div class="space-y-2">
                <h2 id="aggregation-confirmation-title" class="text-lg font-semibold text-slate-800">Підтвердження</h2>
                <p class="text-sm text-slate-600" data-confirm-message></p>
            </div>
            <div class="flex items-center justify-end gap-3">
                <button
                    type="button"
                    class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-200"
                    data-confirm-cancel
                >
                    Скасувати
                </button>
                <button
                    type="button"
                    class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-red-500"
                    data-confirm-accept
                >
                    Підтвердити
                </button>
            </div>
        </div>
    </div>

    {{-- Edit Aggregation Modal --}}
    <div
        id="edit-aggregation-modal"
        class="fixed inset-0 z-50 hidden items-center justify-center"
        role="dialog"
        aria-modal="true"
    >
        <div class="absolute inset-0 bg-slate-900/50" onclick="closeEditModal()"></div>
        <div class="relative w-full max-w-2xl mx-4 rounded-xl bg-white shadow-xl max-h-[90vh] overflow-y-auto">
            <form id="edit-aggregation-form" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-slate-800">Редагувати агрегацію</h2>
                    <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600">
                        <i class="fa-solid fa-times text-xl"></i>
                    </button>
                </div>

                <input type="hidden" id="edit-main-tag-original" name="original_main_tag">

                <div>
                    <label for="edit-main-tag" class="block text-sm font-medium text-slate-700 mb-1">
                        Головний тег
                    </label>
                    <input
                        type="text"
                        id="edit-main-tag"
                        name="main_tag"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                        required
                        readonly
                    >
                </div>

                <div>
                    <label for="edit-category" class="block text-sm font-medium text-slate-700 mb-1">
                        Категорія
                    </label>
                    <div class="relative">
                        <input
                            type="text"
                            id="edit-category"
                            name="category"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                            placeholder="Почніть вводити для пошуку..."
                            autocomplete="off"
                        >
                        <div id="edit-category-dropdown" class="hidden absolute z-10 w-full mt-1 bg-white border border-slate-300 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                            @foreach ($categories as $cat)
                                <div class="category-option px-3 py-2 cursor-pointer hover:bg-blue-50 text-sm" data-value="{{ $cat }}">
                                    {{ $cat }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        Схожі теги
                    </label>
                    <div id="edit-similar-tags-container" class="space-y-2">
                        <!-- Similar tags will be populated here -->
                    </div>
                    <button
                        type="button"
                        onclick="addEditTagInput()"
                        class="mt-2 inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50"
                    >
                        + Додати тег
                    </button>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t">
                    <button
                        type="button"
                        onclick="closeEditModal()"
                        class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                    >
                        Скасувати
                    </button>
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-blue-700 focus:outline-none focus:ring"
                    >
                        Зберегти зміни
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Category Modal --}}
    <div
        id="edit-category-modal"
        class="fixed inset-0 z-50 hidden items-center justify-center"
        role="dialog"
        aria-modal="true"
    >
        <div class="absolute inset-0 bg-slate-900/50" onclick="closeCategoryEditModal()"></div>
        <div class="relative w-full max-w-md mx-4 rounded-xl bg-white shadow-xl">
            <form id="edit-category-form" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-slate-800">Редагувати категорію</h2>
                    <button type="button" onclick="closeCategoryEditModal()" class="text-slate-400 hover:text-slate-600">
                        <i class="fa-solid fa-times text-xl"></i>
                    </button>
                </div>

                <input type="hidden" id="category-original-name" name="original_name">

                <div>
                    <label for="category-new-name" class="block text-sm font-medium text-slate-700 mb-1">
                        Нова назва категорії
                    </label>
                    <input
                        type="text"
                        id="category-new-name"
                        name="new_name"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                        required
                    >
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t">
                    <button
                        type="button"
                        onclick="closeCategoryEditModal()"
                        class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                    >
                        Скасувати
                    </button>
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-blue-700 focus:outline-none focus:ring"
                    >
                        Зберегти
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Tag dropdown functionality
        function initTagDropdowns() {
            // Main tag dropdown
            const mainTagInput = document.getElementById('main_tag');
            const mainTagDropdown = document.getElementById('main_tag_dropdown');
            
            if (mainTagInput && mainTagDropdown) {
                setupTagDropdown(mainTagInput, mainTagDropdown);
            }
            
            // Category dropdown
            const categoryInput = document.getElementById('category');
            const categoryDropdown = document.getElementById('category_dropdown');
            
            if (categoryInput && categoryDropdown) {
                setupCategoryDropdown(categoryInput, categoryDropdown);
            }
            
            // Similar tags dropdowns
            setupSimilarTagDropdowns();
        }
        
        function setupCategoryDropdown(input, dropdown) {
            const options = dropdown.querySelectorAll('.category-option');
            
            // Show dropdown on focus
            input.addEventListener('focus', () => {
                filterOptions(input, dropdown, options);
                dropdown.classList.remove('hidden');
            });
            
            // Filter on input
            input.addEventListener('input', () => {
                filterOptions(input, dropdown, options);
            });
            
            // Select option on click
            options.forEach(option => {
                option.addEventListener('click', () => {
                    input.value = option.dataset.value;
                    dropdown.classList.add('hidden');
                });
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', (e) => {
                if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.classList.add('hidden');
                }
            });
        }
        
        function setupTagDropdown(input, dropdown) {
            const options = dropdown.querySelectorAll('.tag-option');
            
            // Show dropdown on focus
            input.addEventListener('focus', () => {
                filterOptions(input, dropdown, options);
                dropdown.classList.remove('hidden');
            });
            
            // Filter on input
            input.addEventListener('input', () => {
                filterOptions(input, dropdown, options);
            });
            
            // Select option on click
            options.forEach(option => {
                option.addEventListener('click', () => {
                    input.value = option.dataset.value;
                    dropdown.classList.add('hidden');
                });
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', (e) => {
                if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.classList.add('hidden');
                }
            });
        }
        
        function filterOptions(input, dropdown, options) {
            const searchTerm = input.value.toLowerCase();
            let visibleCount = 0;
            
            // Track which categories have visible items
            const categoryVisibility = {};
            
            options.forEach(option => {
                const text = option.textContent.toLowerCase();
                const category = option.dataset.category;
                
                if (text.includes(searchTerm)) {
                    option.classList.remove('hidden');
                    visibleCount++;
                    if (category) {
                        categoryVisibility[category] = true;
                    }
                } else {
                    option.classList.add('hidden');
                }
            });
            
            // Show/hide category headers based on whether they have visible items
            const categoryHeaders = dropdown.querySelectorAll('.bg-slate-100');
            categoryHeaders.forEach(header => {
                const categoryName = header.textContent.trim();
                if (categoryVisibility[categoryName]) {
                    header.classList.remove('hidden');
                } else {
                    header.classList.add('hidden');
                }
            });
            
            // Show/hide dropdown based on visible options
            if (visibleCount > 0) {
                dropdown.classList.remove('hidden');
            } else {
                dropdown.classList.add('hidden');
            }
        }
        
        function setupSimilarTagDropdowns() {
            const groups = document.querySelectorAll('.tag-input-group');
            groups.forEach(group => {
                const input = group.querySelector('.similar-tag-input');
                const dropdown = group.querySelector('.tag-dropdown');
                if (input && dropdown) {
                    const options = dropdown.querySelectorAll('.tag-option');
                    setupTagDropdown(input, dropdown);
                }
            });
        }

        function addTagInput() {
            const container = document.getElementById('similar-tags-container');
            const tagsByCategory = @json($tagsByCategory->map(function($tags) { return $tags->pluck('name'); }));
            
            // Build grouped HTML
            let dropdownHTML = '';
            Object.keys(tagsByCategory).forEach(category => {
                dropdownHTML += `<div class="px-3 py-2 bg-slate-100 text-xs font-semibold text-slate-600 sticky top-0">${category}</div>`;
                tagsByCategory[category].forEach(tag => {
                    dropdownHTML += `<div class="tag-option px-3 py-2 cursor-pointer hover:bg-blue-50 text-sm" data-value="${tag}" data-category="${category}">${tag}</div>`;
                });
            });
            
            const newInput = document.createElement('div');
            newInput.className = 'flex gap-2 tag-input-group';
            newInput.innerHTML = `
                <div class="flex-1 relative">
                    <input
                        type="text"
                        name="similar_tags[]"
                        class="similar-tag-input w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                        placeholder="Почніть вводити для пошуку..."
                        autocomplete="off"
                        required
                    >
                    <div class="tag-dropdown hidden absolute z-10 w-full mt-1 bg-white border border-slate-300 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                        ${dropdownHTML}
                    </div>
                </div>
                <button
                    type="button"
                    onclick="removeTagInput(this)"
                    class="rounded-lg border border-red-200 px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50"
                >
                    Видалити
                </button>
            `;
            container.appendChild(newInput);
            
            // Setup dropdown for the new input
            const input = newInput.querySelector('.similar-tag-input');
            const dropdown = newInput.querySelector('.tag-dropdown');
            const options = dropdown.querySelectorAll('.tag-option');
            setupTagDropdown(input, dropdown);
        }

        function removeTagInput(button) {
            const container = document.getElementById('similar-tags-container');
            if (container.children.length > 1) {
                button.closest('.tag-input-group').remove();
            }
        }

        // Real-time search for aggregations with highlighting and auto-expand
        function initAggregationsSearch() {
            const searchInput = document.getElementById('search-aggregations');
            if (!searchInput) return;

            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase().trim();
                const categoryBlocks = document.querySelectorAll('.aggregation-category-block');

                // Remove all highlights first
                removeAllHighlights();

                categoryBlocks.forEach((block, index) => {
                    const category = block.dataset.category || '';
                    const tags = block.dataset.tags || '';
                    
                    const categoryMatches = searchTerm && category.includes(searchTerm);
                    const tagsMatch = searchTerm && tags.includes(searchTerm);
                    const matches = categoryMatches || tagsMatch;
                    
                    if (matches || searchTerm === '') {
                        block.classList.remove('hidden');
                        
                        // Highlight matches if search term is present
                        if (searchTerm) {
                            // Highlight category name
                            if (categoryMatches) {
                                const categoryNameEl = block.querySelector('.category-name');
                                if (categoryNameEl) {
                                    highlightText(categoryNameEl, searchTerm);
                                }
                            }
                            
                            // Highlight main tags and similar tags
                            if (tagsMatch) {
                                // Auto-expand the category to show matched tags
                                const categoryContent = document.getElementById(`category-${index}`);
                                const icon = document.getElementById(`icon-${index}`);
                                if (categoryContent && categoryContent.classList.contains('hidden')) {
                                    categoryContent.classList.remove('hidden');
                                    if (icon) {
                                        icon.classList.remove('fa-chevron-right');
                                        icon.classList.add('fa-chevron-down');
                                    }
                                }
                                
                                // Highlight matching tags
                                const mainTagEls = block.querySelectorAll('.main-tag-text');
                                const similarTagEls = block.querySelectorAll('.similar-tag-text');
                                
                                mainTagEls.forEach(el => {
                                    if (el.textContent.toLowerCase().includes(searchTerm)) {
                                        highlightText(el, searchTerm);
                                    }
                                });
                                
                                similarTagEls.forEach(el => {
                                    if (el.textContent.toLowerCase().includes(searchTerm)) {
                                        highlightText(el, searchTerm);
                                    }
                                });
                            }
                        }
                    } else {
                        block.classList.add('hidden');
                    }
                });

                // Show "no results" message if needed
                const visibleBlocks = document.querySelectorAll('.aggregation-category-block:not(.hidden)');
                let noResultsMsg = document.getElementById('no-aggregations-results');
                
                if (visibleBlocks.length === 0 && searchTerm !== '') {
                    if (!noResultsMsg) {
                        noResultsMsg = document.createElement('div');
                        noResultsMsg.id = 'no-aggregations-results';
                        noResultsMsg.className = 'text-sm text-slate-500 text-center py-8 rounded-xl border border-slate-200 bg-white';
                        noResultsMsg.textContent = 'Нічого не знайдено за вашим запитом.';
                        document.getElementById('aggregations-list').appendChild(noResultsMsg);
                    }
                } else if (noResultsMsg) {
                    noResultsMsg.remove();
                }
            });
        }

        // Highlight matching text
        function highlightText(element, searchTerm) {
            const text = element.textContent;
            const lowerText = text.toLowerCase();
            const lowerSearch = searchTerm.toLowerCase();
            const index = lowerText.indexOf(lowerSearch);
            
            if (index !== -1) {
                const before = text.substring(0, index);
                const match = text.substring(index, index + searchTerm.length);
                const after = text.substring(index + searchTerm.length);
                
                element.innerHTML = `${escapeHtml(before)}<mark class="bg-yellow-200 px-1 rounded">${escapeHtml(match)}</mark>${escapeHtml(after)}`;
            }
        }

        // Remove all highlights
        function removeAllHighlights() {
            document.querySelectorAll('.category-name, .main-tag-text, .similar-tag-text').forEach(el => {
                if (el.querySelector('mark')) {
                    el.textContent = el.textContent; // Reset to plain text
                }
            });
        }

        // Escape HTML to prevent XSS
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Generate prompt for AI
        function generatePrompt() {
            const button = document.getElementById('btn-generate-prompt');
            button.disabled = true;
            button.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Генерація...';

            // Get the prompt from the controller
            fetch('{{ route("test-tags.aggregations.generate-prompt") }}', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.prompt) {
                    document.getElementById('generated-prompt').textContent = data.prompt;
                    document.getElementById('step-display-prompt').classList.remove('hidden');
                    document.getElementById('step-continue').classList.remove('hidden');
                    button.innerHTML = '<i class="fa-solid fa-check mr-2"></i>Промпт згенеровано';
                } else {
                    alert('Помилка генерації промпту');
                    button.disabled = false;
                    button.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles mr-2"></i>Згенерувати промпт';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Помилка генерації промпту');
                button.disabled = false;
                button.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles mr-2"></i>Згенерувати промпт';
            });
        }

        // Copy prompt to clipboard
        function copyPromptToClipboard() {
            const promptText = document.getElementById('generated-prompt').textContent;
            
            navigator.clipboard.writeText(promptText).then(() => {
                const button = event.target.closest('button');
                const originalHtml = button.innerHTML;
                button.innerHTML = '<i class="fa-solid fa-check mr-2"></i>Скопійовано!';
                button.classList.add('bg-green-50', 'text-green-700', 'border-green-300');
                
                setTimeout(() => {
                    button.innerHTML = originalHtml;
                    button.classList.remove('bg-green-50', 'text-green-700', 'border-green-300');
                }, 2000);
            }).catch(err => {
                console.error('Failed to copy:', err);
                alert('Не вдалося скопіювати в буфер обміну');
            });
        }

        // Toggle paste JSON section
        function togglePasteJson() {
            const section = document.getElementById('paste-json-section');
            if (section.classList.contains('hidden')) {
                section.classList.remove('hidden');
            } else {
                section.classList.add('hidden');
            }
        }

        // Confirmation modal logic (similar to test-tags index page)
        const initAggregationConfirmation = () => {
            const modal = document.getElementById('aggregation-confirmation-modal');
            const messageTarget = modal ? modal.querySelector('[data-confirm-message]') : null;
            const acceptButton = modal ? modal.querySelector('[data-confirm-accept]') : null;
            const cancelButton = modal ? modal.querySelector('[data-confirm-cancel]') : null;
            const overlay = modal ? modal.querySelector('[data-confirm-overlay]') : null;
            const forms = document.querySelectorAll('form[data-confirm]');

            if (!modal || !messageTarget || !acceptButton || !cancelButton) {
                forms.forEach((form) => {
                    form.addEventListener('submit', (event) => {
                        if (form.dataset.confirmed === 'true') {
                            form.dataset.confirmed = '';
                            return;
                        }

                        const message = form.dataset.confirm || '';
                        if (message && !window.confirm(message)) {
                            event.preventDefault();
                        }
                    });
                });

                return;
            }

            let pendingForm = null;

            const closeModal = (restoreFocus = true) => {
                modal.classList.add('hidden');
                modal.classList.remove('flex', 'items-center', 'justify-center');

                if (restoreFocus && pendingForm) {
                    const focusTarget = pendingForm.querySelector('button, [type="submit"], [tabindex]:not([tabindex="-1"])');
                    if (focusTarget && typeof focusTarget.focus === 'function') {
                        focusTarget.focus();
                    }
                }

                pendingForm = null;
            };

            const openModal = (form, message) => {
                messageTarget.textContent = message || 'Підтвердьте дію.';
                pendingForm = form;
                modal.classList.remove('hidden');
                modal.classList.add('flex', 'items-center', 'justify-center');
                acceptButton.focus();
            };

            const handleFormSubmit = (event) => {
                const form = event.target;

                if (form.dataset.confirmed === 'true') {
                    form.dataset.confirmed = '';
                    return;
                }

                event.preventDefault();
                const message = form.dataset.confirm || '';
                openModal(form, message);
            };

            forms.forEach((form) => {
                form.addEventListener('submit', handleFormSubmit);
            });

            acceptButton.addEventListener('click', () => {
                if (!pendingForm) {
                    closeModal();
                    return;
                }

                const formToSubmit = pendingForm;
                formToSubmit.dataset.confirmed = 'true';
                closeModal(false);
                formToSubmit.requestSubmit();
            });

            const cancelHandler = () => {
                closeModal();
            };

            cancelButton.addEventListener('click', cancelHandler);

            if (overlay) {
                overlay.addEventListener('click', cancelHandler);
            }

            window.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                    cancelHandler();
                }
            });
        };

        // Copy JSON to clipboard
        function copyJsonToClipboard() {
            const jsonDisplay = document.getElementById('json-display');
            const text = jsonDisplay.textContent;
            
            navigator.clipboard.writeText(text).then(() => {
                // Show temporary success message
                const button = event.target.closest('button');
                const originalHtml = button.innerHTML;
                button.innerHTML = '<i class="fa-solid fa-check mr-2"></i>Скопійовано!';
                button.classList.add('bg-green-50', 'text-green-700', 'border-green-300');
                
                setTimeout(() => {
                    button.innerHTML = originalHtml;
                    button.classList.remove('bg-green-50', 'text-green-700', 'border-green-300');
                }, 2000);
            }).catch(err => {
                console.error('Failed to copy:', err);
                alert('Не вдалося скопіювати в буфер обміну');
            });
        }

        // Toggle aggregation category visibility
        function toggleAggregationCategory(index) {
            const content = document.getElementById(`category-${index}`);
            const icon = document.getElementById(`icon-${index}`);
            
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                icon.classList.remove('fa-chevron-right');
                icon.classList.add('fa-chevron-down');
            } else {
                content.classList.add('hidden');
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-right');
            }
        }

        // Toggle import form visibility
        function toggleImportForm() {
            const container = document.getElementById('import-form-container');
            const button = document.getElementById('toggle-import-btn');
            
            if (container.classList.contains('hidden')) {
                container.classList.remove('hidden');
                button.innerHTML = '<i class="fa-solid fa-chevron-up mr-2"></i>Сховати форму імпорту';
            } else {
                container.classList.add('hidden');
                button.innerHTML = '<i class="fa-solid fa-chevron-down mr-2"></i>Показати форму імпорту';
            }
        }

        // Auto-show import form if there's old input or errors
        function autoShowImportForm() {
            @if(old('json_data') || $errors->has('json_data'))
                const container = document.getElementById('import-form-container');
                const button = document.getElementById('toggle-import-btn');
                if (container && button) {
                    container.classList.remove('hidden');
                    button.innerHTML = '<i class="fa-solid fa-chevron-up mr-2"></i>Сховати форму імпорту';
                }
            @endif
        }

        // Edit aggregation modal functions
        function editAggregation(mainTag, similarTags, category) {
            const modal = document.getElementById('edit-aggregation-modal');
            const form = document.getElementById('edit-aggregation-form');
            
            // Set form action
            form.action = `{{ route('test-tags.aggregations.index') }}/${encodeURIComponent(mainTag)}`;
            
            // Set main tag (readonly)
            document.getElementById('edit-main-tag').value = mainTag;
            document.getElementById('edit-main-tag-original').value = mainTag;
            
            // Set category
            document.getElementById('edit-category').value = category || '';
            
            // Populate similar tags
            const container = document.getElementById('edit-similar-tags-container');
            container.innerHTML = '';
            
            const tagsByCategory = @json($tagsByCategory->map(function($tags) { return $tags->pluck('name'); }));
            
            similarTags.forEach(tag => {
                addEditTagInputWithValue(tag, tagsByCategory);
            });
            
            // Show modal
            modal.classList.remove('hidden');
            modal.classList.add('flex', 'items-center', 'justify-center');
            
            // Setup category dropdown
            setupEditCategoryDropdown();
        }

        function closeEditModal() {
            const modal = document.getElementById('edit-aggregation-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex', 'items-center', 'justify-center');
        }

        function addEditTagInput() {
            const tagsByCategory = @json($tagsByCategory->map(function($tags) { return $tags->pluck('name'); }));
            addEditTagInputWithValue('', tagsByCategory);
        }

        function addEditTagInputWithValue(value, tagsByCategory) {
            const container = document.getElementById('edit-similar-tags-container');
            
            // Build grouped HTML
            let dropdownHTML = '';
            Object.keys(tagsByCategory).forEach(category => {
                dropdownHTML += `<div class="px-3 py-2 bg-slate-100 text-xs font-semibold text-slate-600 sticky top-0">${category}</div>`;
                tagsByCategory[category].forEach(tag => {
                    dropdownHTML += `<div class="tag-option px-3 py-2 cursor-pointer hover:bg-blue-50 text-sm" data-value="${tag}" data-category="${category}">${tag}</div>`;
                });
            });
            
            const newInput = document.createElement('div');
            newInput.className = 'flex gap-2 edit-tag-input-group';
            newInput.innerHTML = `
                <div class="flex-1 relative">
                    <input
                        type="text"
                        name="similar_tags[]"
                        class="edit-similar-tag-input w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                        placeholder="Почніть вводити для пошуку..."
                        autocomplete="off"
                        value="${value}"
                        required
                    >
                    <div class="edit-tag-dropdown hidden absolute z-10 w-full mt-1 bg-white border border-slate-300 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                        ${dropdownHTML}
                    </div>
                </div>
                <button
                    type="button"
                    onclick="removeEditTagInput(this)"
                    class="rounded-lg border border-red-200 px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50"
                >
                    <i class="fa-solid fa-trash"></i>
                </button>
            `;
            container.appendChild(newInput);
            
            // Setup dropdown for the new input
            const input = newInput.querySelector('.edit-similar-tag-input');
            const dropdown = newInput.querySelector('.edit-tag-dropdown');
            setupTagDropdown(input, dropdown);
        }

        function removeEditTagInput(button) {
            const container = document.getElementById('edit-similar-tags-container');
            if (container.children.length > 1) {
                button.closest('.edit-tag-input-group').remove();
            } else {
                // Don't allow removing the last tag
                alert('Необхідно залишити хоча б один схожий тег.');
            }
        }

        function setupEditCategoryDropdown() {
            const input = document.getElementById('edit-category');
            const dropdown = document.getElementById('edit-category-dropdown');
            if (input && dropdown) {
                setupCategoryDropdown(input, dropdown);
            }
        }

        // Edit category name modal functions
        function editCategoryName(categoryIndex, categoryName) {
            const modal = document.getElementById('edit-category-modal');
            const form = document.getElementById('edit-category-form');
            
            // Set form action
            form.action = `{{ route('test-tags.aggregations.index') }}/category/${encodeURIComponent(categoryName)}`;
            
            // Set category name
            document.getElementById('category-original-name').value = categoryName;
            document.getElementById('category-new-name').value = categoryName;
            
            // Show modal
            modal.classList.remove('hidden');
            modal.classList.add('flex', 'items-center', 'justify-center');
        }

        function closeCategoryEditModal() {
            const modal = document.getElementById('edit-category-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex', 'items-center', 'justify-center');
        }

        // Toggle non-aggregated category visibility
        function toggleNonAggregatedCategory(index) {
            const content = document.getElementById(`non-agg-category-${index}`);
            const icon = document.getElementById(`non-agg-icon-${index}`);
            
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                icon.classList.remove('fa-chevron-right');
                icon.classList.add('fa-chevron-down');
            } else {
                content.classList.add('hidden');
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-right');
            }
        }

        // Toggle JSON configuration visibility
        function toggleJsonConfig() {
            const container = document.getElementById('json-config-container');
            const button = document.getElementById('toggle-json-btn');
            
            if (container.classList.contains('hidden')) {
                container.classList.remove('hidden');
                button.innerHTML = '<i class="fa-solid fa-chevron-up mr-2"></i>Сховати JSON';
            } else {
                container.classList.add('hidden');
                button.innerHTML = '<i class="fa-solid fa-chevron-down mr-2"></i>Показати JSON';
            }
        }

        // Drag and Drop Mode
        let isDragDropMode = false;

        function toggleDragDropMode() {
            isDragDropMode = !isDragDropMode;
            const button = document.getElementById('toggle-drag-mode-btn');
            const nonAggregatedTags = document.querySelectorAll('.non-aggregated-tag');
            const dropZones = document.querySelectorAll('.aggregation-drop-zone');
            const aggregationsSection = document.getElementById('aggregations-section');
            const nonAggregatedSection = document.getElementById('non-aggregated-section');
            
            if (isDragDropMode) {
                // Enable drag mode
                button.innerHTML = '<i class="fa-solid fa-times mr-2"></i>Вимкнути Drag & Drop';
                button.classList.remove('border-purple-300', 'bg-purple-50', 'text-purple-700', 'hover:bg-purple-100');
                button.classList.add('border-red-300', 'bg-red-50', 'text-red-700', 'hover:bg-red-100');
                
                // Create wrapper and arrange sections side by side
                if (aggregationsSection && nonAggregatedSection && !document.getElementById('drag-drop-wrapper')) {
                    const wrapper = document.createElement('div');
                    wrapper.id = 'drag-drop-wrapper';
                    wrapper.className = 'grid grid-cols-1 lg:grid-cols-2 gap-6';
                    
                    // Insert wrapper before aggregations section
                    aggregationsSection.parentNode.insertBefore(wrapper, aggregationsSection);
                    
                    // Move both sections into wrapper
                    wrapper.appendChild(aggregationsSection);
                    wrapper.appendChild(nonAggregatedSection);
                    
                    // Update section classes for better layout in side-by-side mode
                    aggregationsSection.classList.add('drag-drop-active');
                    nonAggregatedSection.classList.add('drag-drop-active');
                }
                
                // Make non-aggregated tags draggable
                nonAggregatedTags.forEach(tag => {
                    tag.setAttribute('draggable', 'true');
                    tag.classList.add('cursor-move', 'hover:shadow-lg', 'transition-shadow');
                    tag.addEventListener('dragstart', handleDragStart);
                    tag.addEventListener('dragend', handleDragEnd);
                });
                
                // Make aggregation zones droppable
                dropZones.forEach(zone => {
                    zone.addEventListener('dragover', handleDragOver);
                    zone.addEventListener('drop', handleDrop);
                    zone.addEventListener('dragleave', handleDragLeave);
                    zone.classList.add('transition-colors');
                });
                
                // Auto-expand all categories for easier dragging
                document.querySelectorAll('[id^="category-"]').forEach(el => {
                    if (el.classList.contains('hidden')) {
                        const index = el.id.replace('category-', '');
                        const icon = document.getElementById('icon-' + index);
                        if (icon) {
                            el.classList.remove('hidden');
                            icon.classList.remove('fa-chevron-right');
                            icon.classList.add('fa-chevron-down');
                        }
                    }
                });
                
                document.querySelectorAll('[id^="non-agg-category-"]').forEach(el => {
                    if (el.classList.contains('hidden')) {
                        const index = el.id.replace('non-agg-category-', '');
                        const icon = document.getElementById('non-agg-icon-' + index);
                        if (icon) {
                            el.classList.remove('hidden');
                            icon.classList.remove('fa-chevron-right');
                            icon.classList.add('fa-chevron-down');
                        }
                    }
                });
            } else {
                // Disable drag mode
                button.innerHTML = '<i class="fa-solid fa-hand-pointer mr-2"></i>Увімкнути Drag & Drop';
                button.classList.remove('border-red-300', 'bg-red-50', 'text-red-700', 'hover:bg-red-100');
                button.classList.add('border-purple-300', 'bg-purple-50', 'text-purple-700', 'hover:bg-purple-100');
                
                // Restore original layout
                const wrapper = document.getElementById('drag-drop-wrapper');
                if (wrapper && aggregationsSection && nonAggregatedSection) {
                    const parent = wrapper.parentNode;
                    
                    // Move sections back to parent
                    parent.insertBefore(aggregationsSection, wrapper);
                    parent.insertBefore(nonAggregatedSection, wrapper);
                    
                    // Remove wrapper
                    wrapper.remove();
                    
                    // Remove drag-drop classes
                    aggregationsSection.classList.remove('drag-drop-active');
                    nonAggregatedSection.classList.remove('drag-drop-active');
                }
                
                // Remove draggable from tags
                nonAggregatedTags.forEach(tag => {
                    tag.removeAttribute('draggable');
                    tag.classList.remove('cursor-move', 'hover:shadow-lg', 'transition-shadow');
                    tag.removeEventListener('dragstart', handleDragStart);
                    tag.removeEventListener('dragend', handleDragEnd);
                });
                
                // Remove droppable from zones
                dropZones.forEach(zone => {
                    zone.removeEventListener('dragover', handleDragOver);
                    zone.removeEventListener('drop', handleDrop);
                    zone.removeEventListener('dragleave', handleDragLeave);
                    zone.classList.remove('transition-colors', 'border-green-400', 'bg-green-50');
                });
            }
        }

        let draggedTag = null;

        function handleDragStart(e) {
            draggedTag = e.target;
            e.target.classList.add('opacity-50');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/html', e.target.innerHTML);
        }

        function handleDragEnd(e) {
            e.target.classList.remove('opacity-50');
        }

        function handleDragOver(e) {
            if (e.preventDefault) {
                e.preventDefault();
            }
            e.dataTransfer.dropEffect = 'move';
            
            // Highlight drop zone
            e.currentTarget.classList.add('border-green-400', 'bg-green-50');
            return false;
        }

        function handleDragLeave(e) {
            e.currentTarget.classList.remove('border-green-400', 'bg-green-50');
        }

        function handleDrop(e) {
            if (e.stopPropagation) {
                e.stopPropagation();
            }
            e.preventDefault();
            
            const dropZone = e.currentTarget;
            dropZone.classList.remove('border-green-400', 'bg-green-50');
            
            if (draggedTag) {
                const tagName = draggedTag.dataset.tagName;
                const mainTag = dropZone.dataset.mainTagExact;
                const category = dropZone.dataset.category;
                
                // Confirm before adding
                if (confirm(`Додати тег "${tagName}" до агрегації "${mainTag}"?`)) {
                    addTagToAggregation(tagName, mainTag, category);
                }
            }
            
            return false;
        }

        function addTagToAggregation(tagName, mainTag, category) {
            // Show loading indicator
            const button = document.getElementById('toggle-drag-mode-btn');
            const originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Додавання...';
            
            // Parse current aggregations from the page
            const aggregations = @json($aggregations);
            
            // Find the aggregation and add the tag
            const aggregation = aggregations.find(a => a.main_tag === mainTag);
            if (!aggregation) {
                alert('Агрегацію не знайдено');
                button.disabled = false;
                button.innerHTML = originalText;
                return;
            }
            
            if (!aggregation.similar_tags) {
                aggregation.similar_tags = [];
            }
            if (!aggregation.similar_tags.includes(tagName)) {
                aggregation.similar_tags.push(tagName);
            }
            
            // Construct the correct URL using Laravel route
            const updateUrl = '{{ route("test-tags.aggregations.update", ["mainTag" => "MAIN_TAG_PLACEHOLDER"]) }}'.replace('MAIN_TAG_PLACEHOLDER', encodeURIComponent(mainTag));
            
            // Send update request
            fetch(updateUrl, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    similar_tags: aggregation.similar_tags,
                    category: category
                })
            })
            .then(response => {
                if (response.ok) {
                    // Reload page to show updated aggregations
                    window.location.reload();
                } else {
                    return response.text().then(text => {
                        console.error('Server response:', text);
                        throw new Error('Помилка оновлення агрегації (статус: ' + response.status + ')');
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Помилка при додаванні тегу до агрегації: ' + error.message);
                button.disabled = false;
                button.innerHTML = originalText;
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                initTagDropdowns();
                initAggregationConfirmation();
                autoShowImportForm();
                initAggregationsSearch();
            });
        } else {
            initTagDropdowns();
            initAggregationConfirmation();
            autoShowImportForm();
            initAggregationsSearch();
        }
    </script>
@endpush
