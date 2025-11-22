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
                @input="filterTags"
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
                    <div x-show="isCategoryVisible('{{ $categoryName }}')" x-transition>
                        <div class="mb-2 flex items-center justify-between">
                            <h4 class="text-sm font-semibold text-slate-700">{{ $categoryName }}</h4>
                            <span class="text-xs text-slate-400" x-text="getVisibleTagsCount('{{ $categoryName }}')"></span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 md:grid-cols-3">
                            @foreach ($tags as $tag)
                                <label 
                                    class="flex items-center space-x-2 rounded border border-slate-200 bg-white px-3 py-2 hover:bg-blue-50 cursor-pointer transition-colors"
                                    x-show="isTagVisible({{ $tag->id }}, '{{ $tag->name }}', '{{ $categoryName }}')"
                                    x-transition
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
        visibleTags: {},
        visibleCategories: {},
        
        init() {
            this.updateVisibility();
        },
        
        filterTags() {
            this.updateVisibility();
        },
        
        updateVisibility() {
            const query = this.searchQuery.toLowerCase().trim();
            
            if (!query) {
                // Show all
                this.visibleTags = {};
                this.visibleCategories = {};
                return;
            }
            
            // Mark visible tags and categories
            const tags = document.querySelectorAll('[x-show*="isTagVisible"]');
            tags.forEach(el => {
                const tagName = el.querySelector('span').textContent.toLowerCase();
                const categoryName = el.closest('[x-show*="isCategoryVisible"]')?.querySelector('h4')?.textContent.toLowerCase() || '';
                
                const visible = tagName.includes(query) || categoryName.includes(query);
                const tagId = parseInt(el.querySelector('input').value);
                
                this.visibleTags[tagId] = visible;
                
                if (visible && categoryName) {
                    this.visibleCategories[categoryName] = true;
                }
            });
        },
        
        isTagVisible(tagId, tagName, categoryName) {
            const query = this.searchQuery.toLowerCase().trim();
            
            if (!query) return true;
            
            return tagName.toLowerCase().includes(query) || 
                   categoryName.toLowerCase().includes(query);
        },
        
        isCategoryVisible(categoryName) {
            const query = this.searchQuery.toLowerCase().trim();
            
            if (!query) return true;
            
            // Show category if its name matches or if it has visible tags
            if (categoryName.toLowerCase().includes(query)) {
                return true;
            }
            
            // Check if any tag in this category is visible
            const categoryDiv = document.querySelector(`h4:contains('${categoryName}')`);
            if (categoryDiv) {
                const labels = categoryDiv.closest('[x-show*="isCategoryVisible"]')?.querySelectorAll('label') || [];
                for (let label of labels) {
                    const tagName = label.querySelector('span')?.textContent.toLowerCase() || '';
                    if (tagName.includes(query)) {
                        return true;
                    }
                }
            }
            
            return false;
        },
        
        getVisibleTagsCount(categoryName) {
            const query = this.searchQuery.toLowerCase().trim();
            
            if (!query) {
                // Count all tags in category
                const categoryDiv = Array.from(document.querySelectorAll('h4')).find(h => h.textContent === categoryName);
                const count = categoryDiv?.closest('[x-show*="isCategoryVisible"]')?.querySelectorAll('label').length || 0;
                return count + ' ' + (count === 1 ? 'тег' : 'тегів');
            }
            
            // Count visible tags
            const categoryDiv = Array.from(document.querySelectorAll('h4')).find(h => h.textContent === categoryName);
            const labels = categoryDiv?.closest('[x-show*="isCategoryVisible"]')?.querySelectorAll('label') || [];
            let visibleCount = 0;
            
            for (let label of labels) {
                const tagName = label.querySelector('span')?.textContent.toLowerCase() || '';
                if (tagName.includes(query) || categoryName.toLowerCase().includes(query)) {
                    visibleCount++;
                }
            }
            
            return visibleCount + ' ' + (visibleCount === 1 ? 'тег' : 'тегів');
        },
        
        hasVisibleTags() {
            const query = this.searchQuery.toLowerCase().trim();
            
            if (!query) return true;
            
            const labels = document.querySelectorAll('[x-show*="isTagVisible"]');
            for (let label of labels) {
                const tagName = label.querySelector('span')?.textContent.toLowerCase() || '';
                const categoryName = label.closest('[x-show*="isCategoryVisible"]')?.querySelector('h4')?.textContent.toLowerCase() || '';
                
                if (tagName.includes(query) || categoryName.includes(query)) {
                    return true;
                }
            }
            
            return false;
        }
    }
}
</script>
@endpush
