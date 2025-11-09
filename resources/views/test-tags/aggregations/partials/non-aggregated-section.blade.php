<section class="space-y-4" id="non-aggregated-section">
    <div class="flex items-center justify-between gap-4">
        <h2 class="text-xl font-semibold text-slate-800">Неагреговані теги</h2>
        @if (!$nonAggregatedTags->isEmpty())
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    id="create-category-btn"
                    onclick="openCreateCategoryModal()"
                    class="hidden inline-flex items-center rounded-lg border border-blue-300 bg-blue-50 px-4 py-2 text-sm font-medium text-blue-700 hover:bg-blue-100 focus:outline-none focus:ring"
                >
                    <i class="fa-solid fa-plus mr-2"></i>Створити категорію
                </button>
                <button
                    type="button"
                    id="toggle-drag-mode-btn"
                    onclick="toggleDragDropMode()"
                    class="inline-flex items-center rounded-lg border border-purple-300 bg-purple-50 px-4 py-2 text-sm font-medium text-purple-700 hover:bg-purple-100 focus:outline-none focus:ring"
                >
                    <i class="fa-solid fa-hand-pointer mr-2"></i>Увімкнути Drag & Drop
                </button>
            </div>
        @endif
    </div>
    @if ($nonAggregatedTags->isEmpty())
        <p class="text-sm text-slate-500 rounded-xl border border-slate-200 bg-white p-6">
            Всі теги вже агреговані.
        </p>
    @else
        <div class="space-y-4" id="non-aggregated-list">
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
                                <p class="text-sm text-slate-500 non-aggregated-count">{{ count($tags) }} {{ count($tags) === 1 ? 'тег' : 'тегів' }}</p>
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
    <p id="all-tags-aggregated-message" class="{{ $nonAggregatedTags->isEmpty() ? '' : 'hidden' }} text-sm text-slate-500 rounded-xl border border-slate-200 bg-white p-6">
        Всі теги вже агреговані.
    </p>
</section>
