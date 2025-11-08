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
                        <form method="POST" action="{{ route('test-tags.aggregations.auto') }}" class="inline">
                            @csrf
                            <button
                                type="submit"
                                class="inline-flex items-center justify-center rounded-lg bg-purple-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-purple-700 focus:outline-none focus:ring"
                            >
                                <i class="fa-solid fa-wand-magic-sparkles mr-2"></i>Автогенерація (Gemini)
                            </button>
                        </form>
                        <form method="POST" action="{{ route('test-tags.aggregations.auto-chatgpt') }}" class="inline">
                            @csrf
                            <button
                                type="submit"
                                class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-blue-700 focus:outline-none focus:ring"
                            >
                                <i class="fa-solid fa-robot mr-2"></i>Автогенерація (ChatGPT)
                            </button>
                        </form>
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

            <section class="space-y-4">
                <h2 class="text-xl font-semibold text-slate-800">Існуючі агрегації</h2>
                @if (empty($aggregations))
                    <p class="text-sm text-slate-500 rounded-xl border border-slate-200 bg-white p-6">
                        Агрегації ще не створено.
                    </p>
                @else
                    <div class="space-y-4">
                        @foreach ($aggregations as $aggregation)
                            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                                <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
                                    <div>
                                        <h3 class="text-lg font-semibold text-slate-800">
                                            {{ $aggregation['main_tag'] }}
                                        </h3>
                                        <p class="text-sm text-slate-500">Головний тег</p>
                                        @if (!empty($aggregation['category']))
                                            <p class="text-xs text-slate-400 mt-1">
                                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700">
                                                    {{ $aggregation['category'] }}
                                                </span>
                                            </p>
                                        @endif
                                    </div>
                                    <form
                                        action="{{ route('test-tags.aggregations.destroy', ['mainTag' => $aggregation['main_tag']]) }}"
                                        method="POST"
                                        data-confirm="Видалити агрегацію для тегу «{{ $aggregation['main_tag'] }}»?"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="inline-flex items-center rounded-lg border border-red-200 px-3 py-1.5 text-sm font-medium text-red-600 hover:bg-red-50"
                                        >
                                            Видалити
                                        </button>
                                    </form>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-slate-700 mb-2">Схожі теги:</p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($aggregation['similar_tags'] ?? [] as $similarTag)
                                            <span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-sm font-medium text-blue-800">
                                                {{ $similarTag }}
                                            </span>
                                        @endforeach
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
                <h2 class="text-xl font-semibold text-slate-800">JSON конфігурація</h2>
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

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                initTagDropdowns();
                initAggregationConfirmation();
            });
        } else {
            initTagDropdowns();
            initAggregationConfirmation();
        }
    </script>
@endpush
