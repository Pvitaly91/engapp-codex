{{-- 
    Nested category navigation partial
    @param Collection $categories - Root categories with children
    @param ?PageCategory $selectedCategory - Currently selected category
    @param string $routePrefix - Route prefix (e.g., 'theory')
    @param int $level - Current nesting level (default 0)
--}}
@php
    $categories = $categories ?? collect();
    $selectedCategory = $selectedCategory ?? null;
    $routePrefix = $routePrefix ?? 'theory';
    $level = $level ?? 0;
@endphp

@foreach($categories as $category)
    @php
        $isActive = $selectedCategory && $selectedCategory->is($category);
        $hasChildren = $category->hasChildren();
        $hasSelectedDescendant = $hasChildren && $category->hasDescendant($selectedCategory);
        $isExpanded = $isActive || $hasSelectedDescendant;
    @endphp
    
    <div class="category-item" x-data="{ expanded: {{ $isExpanded ? 'true' : 'false' }} }">
        <div class="flex items-center gap-1">
            @if($hasChildren)
                <button 
                    @click="expanded = !expanded"
                    class="flex h-6 w-6 items-center justify-center rounded text-muted-foreground hover:text-foreground hover:bg-muted/50 transition-colors"
                    type="button"
                >
                    <svg 
                        class="h-3.5 w-3.5 transition-transform duration-200"
                        :class="{ 'rotate-90': expanded }"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            @else
                <span class="w-6"></span>
            @endif
            
            <a 
                href="{{ route($routePrefix . '.category', $category->slug) }}"
                class="flex-1 flex items-center justify-between gap-2 rounded-lg px-2 py-1.5 text-sm transition-all {{ $isActive ? 'bg-primary text-primary-foreground font-medium' : 'text-muted-foreground hover:text-foreground hover:bg-muted/50' }}"
            >
                <span class="truncate">{{ $category->title }}</span>
                @if(isset($category->pages_count) && $category->pages_count > 0)
                    <span class="text-xs opacity-60">{{ $category->pages_count }}</span>
                @endif
            </a>
        </div>
        
        @if($hasChildren)
            <div 
                x-show="expanded" 
                x-collapse
                class="ml-4 mt-1 space-y-1 border-l border-border/40 pl-2"
            >
                @include('engram.theory.partials.nested-category-nav', [
                    'categories' => $category->children,
                    'selectedCategory' => $selectedCategory,
                    'routePrefix' => $routePrefix,
                    'level' => $level + 1,
                ])
            </div>
        @endif
    </div>
@endforeach
