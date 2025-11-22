@props(['allTags', 'selectedTags' => [], 'name' => 'tags', 'helpText' => 'Виберіть теги'])

@php
    // Group tags by category
    $groupedTags = $allTags->groupBy(function($tag) {
        return $tag->category ?: 'Без категорії';
    })->sortKeys();
    
    // Move "Без категорії" to the end
    if ($groupedTags->has('Без категорії')) {
        $uncategorized = $groupedTags->pull('Без категорії');
        $groupedTags->put('Без категорії', $uncategorized);
    }
    
    // Prepare data for JavaScript
    $categoryTagsData = $groupedTags->map(function($tags, $cat) {
        return [
            'name' => $cat,
            'tags' => $tags->map(function($tag) {
                return [
                    'id' => $tag->id,
                    'name' => $tag->name
                ];
            })->values()
        ];
    })->values();
@endphp

<div class="space-y-2" x-data="tagSelector(@js($selectedTags))">
    <label class="block text-sm font-medium text-slate-700">Теги</label>
    
    @if($allTags->isEmpty())
        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
            <p class="text-sm text-slate-500">Теги ще не створено. <a href="{{ route('test-tags.create') }}" class="text-blue-600 hover:underline">Створити тег</a></p>
        </div>
    @else
        <!-- Search input -->
        <div class="relative">
            <input
                type="text"
                x-model="searchQuery"
                placeholder="Пошук тегів або категорій..."
                class="w-full rounded-lg border border-slate-300 px-3 py-2 pl-10 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring"
            >
            <svg class="absolute left-3 top-2.5 h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </div>

        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 max-h-96 overflow-y-auto">
            <div class="space-y-4">
                @foreach ($groupedTags as $categoryName => $tags)
                    <div x-show="isCategoryVisible({{ json_encode($categoryName) }})" x-transition data-category="{{ e($categoryName) }}">
                        <div class="mb-2 flex items-center justify-between">
                            <h4 class="text-sm font-semibold text-slate-700">{{ $categoryName }}</h4>
                            <span class="text-xs text-slate-400" x-text="getVisibleTagsCount({{ json_encode($categoryName) }})"></span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 md:grid-cols-3">
                            @foreach ($tags as $tag)
                                <label 
                                    class="flex items-center space-x-2 rounded border border-slate-200 bg-white px-3 py-2 hover:bg-blue-50 cursor-pointer transition-colors"
                                    x-show="isTagVisible({{ $tag->id }}, {{ json_encode($tag->name) }}, {{ json_encode($categoryName) }})"
                                    x-transition
                                    data-tag-id="{{ $tag->id }}"
                                    data-tag-name="{{ e($tag->name) }}"
                                    data-category="{{ e($categoryName) }}"
                                >
                                    <input
                                        type="checkbox"
                                        name="{{ $name }}[]"
                                        value="{{ $tag->id }}"
                                        x-model="selectedTagIds"
                                        class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                    >
                                    <span class="text-sm text-slate-700">{{ $tag->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
                
                <!-- No results message -->
                <div x-show="!hasVisibleTags()" x-transition class="text-center py-4">
                    <p class="text-sm text-slate-500">Нічого не знайдено</p>
                </div>
            </div>
        </div>

        <!-- Selected tags count -->
        <div class="flex items-center justify-between text-xs">
            <p class="text-slate-400">{{ $helpText }}</p>
            <p class="text-slate-600" x-show="selectedTagIds.length > 0">
                Обрано: <span x-text="selectedTagIds.length" class="font-semibold"></span>
            </p>
        </div>
    @endif
</div>

@push('scripts')
<script>
function tagSelector(initialSelected) {
    return {
        selectedTagIds: Array.isArray(initialSelected) ? initialSelected : [],
        searchQuery: '',
        categoryTags: @json($categoryTagsData),
        
        get normalizedQuery() {
            return this.searchQuery.toLowerCase().trim();
        },
        
        formatTagCount(count) {
            return count + ' ' + (count === 1 ? 'тег' : 'тегів');
        },
        
        isTagVisible(tagId, tagName, categoryName) {
            if (!this.normalizedQuery) return true;
            
            return tagName.toLowerCase().includes(this.normalizedQuery) || 
                   categoryName.toLowerCase().includes(this.normalizedQuery);
        },
        
        isCategoryVisible(categoryName) {
            if (!this.normalizedQuery) return true;
            
            // Show category if its name matches
            if (categoryName.toLowerCase().includes(this.normalizedQuery)) {
                return true;
            }
            
            // Check if any tag in this category is visible
            const category = this.categoryTags.find(c => c.name === categoryName);
            if (category) {
                return category.tags.some(tag => 
                    tag.name.toLowerCase().includes(this.normalizedQuery)
                );
            }
            
            return false;
        },
        
        getVisibleTagsCount(categoryName) {
            const category = this.categoryTags.find(c => c.name === categoryName);
            if (!category) return this.formatTagCount(0);
            
            if (!this.normalizedQuery) {
                return this.formatTagCount(category.tags.length);
            }
            
            // Count visible tags
            const visibleCount = category.tags.filter(tag => 
                tag.name.toLowerCase().includes(this.normalizedQuery) || 
                categoryName.toLowerCase().includes(this.normalizedQuery)
            ).length;
            
            return this.formatTagCount(visibleCount);
        },
        
        hasVisibleTags() {
            if (!this.normalizedQuery) return true;
            
            // Check if any category or tag matches
            return this.categoryTags.some(category => 
                category.name.toLowerCase().includes(this.normalizedQuery) ||
                category.tags.some(tag => tag.name.toLowerCase().includes(this.normalizedQuery))
            );
        }
    }
}
</script>
@endpush
