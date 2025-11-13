<section class="space-y-4" id="aggregations-section">
    <div class="flex items-center justify-between gap-4">
        <h2 class="text-xl font-semibold text-slate-800">Існуючі агрегації</h2>
        @if (!empty($aggregations))
            <div class="flex items-center gap-2 flex-1 justify-end">
                <div class="flex-1 max-w-md">
                    <input
                        type="text"
                        id="search-aggregations"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                        placeholder="Пошук по категоріям та тегам..."
                    >
                </div>
                <div class="flex items-center gap-1">
                    <button
                        type="button"
                        id="expand-all-aggregations-btn"
                        onclick="expandAllAggregations()"
                        class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50"
                        title="Розгорнути всі"
                    >
                        <i class="fa-solid fa-chevron-down mr-1"></i>Всі
                    </button>
                    <button
                        type="button"
                        id="collapse-all-aggregations-btn"
                        onclick="collapseAllAggregations()"
                        class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50"
                        title="Згорнути всі"
                    >
                        <i class="fa-solid fa-chevron-up mr-1"></i>Всі
                    </button>
                </div>
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
                <div
                    class="aggregation-category-block rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden"
                    data-category="{{ strtolower($category) }}"
                    data-tags="{{ strtolower(implode(' ', collect($categoryAggregations)->flatMap(function($a) {
                        $tags = [$a['main_tag']];
                        foreach ($a['similar_tags'] ?? [] as $st) {
                            $tags[] = is_array($st) ? $st['tag'] : $st;
                        }
                        return $tags;
                    })->toArray())) }}"
                >
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
                                <p class="text-sm text-slate-500">
                                    {{ count($categoryAggregations) }} {{ count($categoryAggregations) === 1 ? 'агрегація' : 'агрегацій' }}
                                    @if (isset($categoryCreatedAt[$category]))
                                        <span class="text-slate-400">• {{ \Carbon\Carbon::parse($categoryCreatedAt[$category])->format('d.m.Y H:i') }}</span>
                                    @endif
                                </p>
                            </div>
                        </button>
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                onclick="openCreateAggregationModal('{{ addslashes($category) }}')"
                                class="create-aggregation-btn hidden inline-flex items-center rounded-lg border border-green-300 bg-green-50 px-2 py-1 text-xs font-medium text-green-700 hover:bg-green-100"
                                title="Створити агрегацію"
                            >
                                <i class="fa-solid fa-plus mr-1"></i>Агрегація
                            </button>
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
                                                @if (isset($aggregation['main_tag_created_at']))
                                                    <span class="text-xs font-normal text-slate-500 ml-2">{{ \Carbon\Carbon::parse($aggregation['main_tag_created_at'])->format('d.m.Y H:i') }}</span>
                                                @endif
                                            </h4>
                                            <p class="text-xs text-slate-500">Головний тег</p>
                                        </div>
                                        <div class="flex gap-2">
                                            <button
                                                type="button"
                                                onclick="openEditAggregationModal(this)"
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
                                                @php
                                                    $tagName = is_array($similarTag) ? $similarTag['tag'] : $similarTag;
                                                    $addedAt = is_array($similarTag) && isset($similarTag['added_at']) ? $similarTag['added_at'] : null;
                                                @endphp
                                                <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 similar-tag-badge" data-tag="{{ strtolower($tagName) }}">
                                                    <span class="similar-tag-text">{{ $tagName }}</span>
                                                    @if ($addedAt)
                                                        <span class="text-blue-600 opacity-70 text-[10px]">{{ \Carbon\Carbon::parse($addedAt)->format('d.m.Y') }}</span>
                                                    @endif
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
