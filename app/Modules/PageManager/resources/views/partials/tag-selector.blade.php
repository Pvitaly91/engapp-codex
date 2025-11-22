<section class="space-y-4 rounded-2xl border border-gray-200 bg-white p-6 shadow">
    <header class="space-y-1">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">{{ $label }}</h2>
                <p class="text-sm text-gray-500">{{ $description }}</p>
            </div>
            <label class="relative block w-full max-w-xs">
                <span class="sr-only">Пошук тегів</span>
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-gray-400">
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                </span>
                <input
                    type="search"
                    placeholder="Пошук за назвою тегу або категорією..."
                    class="w-full rounded-xl border border-gray-300 bg-white py-2 pl-9 pr-3 text-sm text-gray-700 placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                    data-tag-search
                />
            </label>
        </div>
        <p class="text-xs text-gray-400">Доступно категорій тегів: {{ $tagsByCategory->count() }}.</p>
    </header>

    @if ($tagsByCategory->isEmpty())
        <p class="text-sm text-gray-500">Ще немає тегів. Додайте їх у розділі «Теги тестів», щоб використовувати тут.</p>
    @else
        <div class="space-y-4" data-tag-selector>
            <div class="grid gap-3 md:grid-cols-1" data-tag-categories>
                @foreach ($tagsByCategory as $tagCategory => $tags)
                    @php
                        $categoryLabel = $tagCategory ?: 'Без категорії';
                        $categoryKey = strtolower($categoryLabel);
                    @endphp
                    <div
                        class="space-y-3 rounded-xl border border-gray-200 bg-gray-50 p-4"
                        data-tag-category
                        data-category-name="{{ $categoryKey }}"
                        x-data="{ expanded: false }"
                    >
                        <div class="flex items-center justify-between gap-2">
                            <button
                                type="button"
                                @click="expanded = !expanded"
                                class="flex flex-1 items-center gap-2 text-left transition-colors hover:text-gray-900"
                            >
                                <svg
                                    class="h-4 w-4 text-gray-500 transition-transform"
                                    :class="{ 'rotate-90': expanded }"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                </svg>
                                <div class="space-y-0.5">
                                    <p class="text-sm font-semibold text-gray-800" data-category-label>{{ $categoryLabel }}</p>
                                    <p class="text-xs text-gray-500">{{ trans_choice('{0}Немає тегів|{1}1 тег|[2,4]:count теги|[5,*]:count тегів', $tags->count(), ['count' => $tags->count()]) }}</p>
                                </div>
                            </button>
                            <span class="inline-flex items-center rounded-full bg-gray-200 px-3 py-1 text-xs font-semibold text-gray-700">{{ $tags->count() }}</span>
                        </div>
                        <div
                            class="grid gap-2"
                            data-tag-options
                            x-show="expanded"
                            x-transition
                        >
                            @foreach ($tags as $tag)
                                @php
                                    $inputId = ($idPrefix ?? 'tag') . '-' . $tag->id;
                                    $isChecked = in_array($tag->id, $selectedTagIds, true);
                                @endphp
                                <label
                                    for="{{ $inputId }}"
                                    class="flex cursor-pointer items-center justify-between rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 transition hover:border-blue-200 hover:bg-blue-50"
                                    data-tag-option
                                    data-tag-name="{{ strtolower($tag->name) }}"
                                    data-tag-category-name="{{ $categoryKey }}"
                                >
                                    <span class="flex items-center gap-2">
                                        <input
                                            id="{{ $inputId }}"
                                            type="checkbox"
                                            name="{{ $inputName ?? 'tags[]' }}"
                                            value="{{ $tag->id }}"
                                            @checked($isChecked)
                                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        />
                                        <span data-tag-name-display>{{ $tag->name }}</span>
                                    </span>
                                    <span class="text-xs text-gray-500">Категорія: {{ $categoryLabel }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            <p class="hidden rounded-lg border border-dashed border-gray-300 bg-gray-50 px-4 py-3 text-sm text-gray-500" data-tag-empty>
                За запитом нічого не знайдено. Спробуйте іншу назву тегу або категорії.
            </p>
        </div>
    @endif
</section>

@push('scripts')
    @once('page-manager-tag-selector')
        <script>
            const initPageManagerTagSelectors = () => {
                document.querySelectorAll('[data-tag-selector]').forEach((selector) => {
                    const searchInput = selector.closest('section').querySelector('[data-tag-search]');
                    const categories = selector.querySelectorAll('[data-tag-category]');
                    const emptyState = selector.querySelector('[data-tag-empty]');

                    // Function to highlight search term in text using safe DOM manipulation
                    const highlightText = (element, text, term) => {
                        if (!term || term.length > 100) {
                            element.textContent = text;
                            return;
                        }
                        
                        const escapedTerm = term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                        const regex = new RegExp(`(${escapedTerm})`, 'gi');
                        const parts = text.split(regex);
                        
                        // Clear element
                        element.textContent = '';
                        
                        // Build content using safe DOM methods
                        parts.forEach((part, index) => {
                            if (part.toLowerCase() === term.toLowerCase()) {
                                const mark = document.createElement('mark');
                                mark.className = 'bg-yellow-200 text-gray-900';
                                mark.textContent = part;
                                element.appendChild(mark);
                            } else if (part) {
                                element.appendChild(document.createTextNode(part));
                            }
                        });
                    };

                    const updateVisibility = () => {
                        const term = (searchInput?.value || '').toLowerCase().trim();
                        let hasVisible = false;

                        categories.forEach((category) => {
                            const categoryName = category.dataset.categoryName || '';
                            const options = category.querySelectorAll('[data-tag-option]');
                            let visibleOptions = 0;

                            options.forEach((option) => {
                                const tagName = option.dataset.tagName || '';
                                const searchable = `${tagName} ${categoryName}`;
                                const matches = term === '' || searchable.includes(term);
                                option.style.display = matches ? '' : 'none';
                                
                                // Highlight matching text
                                const tagTextSpan = option.querySelector('[data-tag-name-display]');
                                if (tagTextSpan) {
                                    let originalText = tagTextSpan.getAttribute('data-original-text');
                                    if (!originalText) {
                                        originalText = tagTextSpan.textContent;
                                        tagTextSpan.setAttribute('data-original-text', originalText);
                                    }
                                    highlightText(tagTextSpan, originalText, term);
                                }
                                
                                if (matches) {
                                    visibleOptions += 1;
                                }
                            });

                            // Highlight matching text in category label
                            const categoryLabelElement = category.querySelector('[data-category-label]');
                            if (categoryLabelElement) {
                                let originalCategoryText = categoryLabelElement.getAttribute('data-original-text');
                                if (!originalCategoryText) {
                                    originalCategoryText = categoryLabelElement.textContent;
                                    categoryLabelElement.setAttribute('data-original-text', originalCategoryText);
                                }
                                highlightText(categoryLabelElement, originalCategoryText, term);
                            }

                            // Auto-expand categories with matching tags when searching
                            if (term && visibleOptions > 0) {
                                // Trigger Alpine.js to expand the category
                                const button = category.querySelector('button[type="button"]');
                                if (button && button.__x) {
                                    const alpineData = button.__x.$data;
                                    if (alpineData && !alpineData.expanded) {
                                        alpineData.expanded = true;
                                    }
                                }
                            }

                            category.style.display = visibleOptions > 0 ? '' : 'none';
                            if (visibleOptions > 0) {
                                hasVisible = true;
                            }
                        });

                        if (emptyState) {
                            emptyState.classList.toggle('hidden', hasVisible);
                        }
                    };

                    searchInput?.addEventListener('input', updateVisibility);
                    updateVisibility();
                });
            };

            document.addEventListener('DOMContentLoaded', initPageManagerTagSelectors);
        </script>
    @endonce
@endpush
