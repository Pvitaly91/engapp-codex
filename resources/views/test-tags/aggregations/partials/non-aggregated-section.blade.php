<section class="space-y-4" id="non-aggregated-section">
    <div class="flex items-center justify-between gap-4">
        <h2 class="text-xl font-semibold text-slate-800">Неагреговані теги</h2>
        @if (!$nonAggregatedTags->isEmpty())
            <div class="flex items-center gap-2 flex-1 justify-end">
                <div class="flex-1 max-w-md">
                    <input
                        type="text"
                        id="search-non-aggregated"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                        placeholder="Пошук по категоріям та тегам..."
                    >
                </div>
                <div class="flex items-center gap-1">
                    <button
                        type="button"
                        id="expand-all-non-aggregated-btn"
                        onclick="expandAllNonAggregated()"
                        class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50"
                        title="Розгорнути всі"
                    >
                        <i class="fa-solid fa-chevron-down mr-1"></i>Всі
                    </button>
                    <button
                        type="button"
                        id="collapse-all-non-aggregated-btn"
                        onclick="collapseAllNonAggregated()"
                        class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50"
                        title="Згорнути всі"
                    >
                        <i class="fa-solid fa-chevron-up mr-1"></i>Всі
                    </button>
                </div>
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
                <div class="non-aggregated-category-block rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden"
                     data-category="{{ strtolower($category) }}"
                     data-tags="{{ strtolower(implode(' ', $tags->pluck('name')->toArray())) }}">
                    {{-- Category Header --}}
                    <div class="flex items-center justify-between px-6 py-4 bg-slate-50">
                        <button
                            type="button"
                            onclick="toggleNonAggregatedCategory('{{ $loop->index }}')"
                            class="flex items-center gap-3 flex-1 text-left hover:opacity-80 transition-opacity"
                        >
                            <i id="non-agg-icon-{{ $loop->index }}" class="fa-solid fa-chevron-right text-slate-400 transition-transform"></i>
                            <div>
                                <h3 class="text-lg font-semibold text-slate-800 non-agg-category-name">{{ $category }}</h3>
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
