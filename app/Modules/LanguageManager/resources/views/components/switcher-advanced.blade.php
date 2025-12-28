{{-- Advanced Language Switcher Component --}}
{{-- Usage: @include('language-manager::components.switcher-advanced') --}}
{{-- Designed for 20+ languages with search, scroll, and keyboard navigation --}}
{{-- Requires Alpine.js --}}

@php
    $languages = $__languageSwitcher ?? [];
    $currentLocale = $__currentLocale ?? app()->getLocale();
    
    // Find current language info
    $currentLang = collect($languages)->firstWhere('is_current', true);
    $currentName = $currentLang['native_name'] ?? strtoupper($currentLocale);
    $currentCode = $currentLang['code'] ?? $currentLocale;
    
    // Get flag emoji for language code
    $flags = [
        'uk' => '🇺🇦',
        'en' => '🇬🇧',
        'pl' => '🇵🇱',
        'de' => '🇩🇪',
        'fr' => '🇫🇷',
        'es' => '🇪🇸',
        'it' => '🇮🇹',
        'pt' => '🇵🇹',
        'nl' => '🇳🇱',
        'ru' => '🇷🇺',
        'ja' => '🇯🇵',
        'ko' => '🇰🇷',
        'zh' => '🇨🇳',
        'ar' => '🇸🇦',
        'tr' => '🇹🇷',
        'sv' => '🇸🇪',
        'no' => '🇳🇴',
        'da' => '🇩🇰',
        'fi' => '🇫🇮',
        'cs' => '🇨🇿',
        'sk' => '🇸🇰',
        'hu' => '🇭🇺',
        'ro' => '🇷🇴',
        'bg' => '🇧🇬',
        'el' => '🇬🇷',
        'he' => '🇮🇱',
        'th' => '🇹🇭',
        'vi' => '🇻🇳',
        'id' => '🇮🇩',
        'ms' => '🇲🇾',
        'hi' => '🇮🇳',
    ];
    $currentFlag = $flags[$currentCode] ?? '🌐';
@endphp

@if(count($languages) > 0)
<div 
    x-data="languageSwitcher()" 
    class="relative"
    @keydown.escape.window="open = false"
    @click.away="open = false"
>
    {{-- Trigger button --}}
    <button 
        type="button"
        @click="toggle()"
        @keydown.arrow-down.prevent="open = true; $nextTick(() => focusFirst())"
        class="flex items-center gap-2 rounded-xl border border-border bg-background px-3 py-2 text-sm font-medium transition hover:border-primary hover:bg-muted focus:outline-none focus:ring-2 focus:ring-primary/50"
        :aria-expanded="open"
        aria-haspopup="listbox"
        aria-label="{{ __('public.language.select_language') }}"
    >
        <span class="text-base">{{ $currentFlag }}</span>
        <span class="hidden sm:inline">{{ strtoupper($currentCode) }}</span>
        <svg 
            class="h-4 w-4 text-muted-foreground transition-transform duration-200" 
            :class="{ 'rotate-180': open }"
            fill="none" 
            stroke="currentColor" 
            viewBox="0 0 24 24"
        >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    {{-- Dropdown panel --}}
    <div 
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute right-0 z-50 mt-2 w-64 origin-top-right rounded-xl border border-border bg-card shadow-lg"
        role="listbox"
        aria-label="{{ __('public.language.available_languages') }}"
        x-cloak
    >
        {{-- Search input (shown only if many languages) --}}
        @if(count($languages) > 5)
        <div class="p-2 border-b border-border">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input 
                    type="text" 
                    x-model="searchQuery"
                    x-ref="searchInput"
                    @keydown.arrow-down.prevent="focusNext()"
                    @keydown.arrow-up.prevent="focusPrev()"
                    @keydown.enter.prevent="selectFocused()"
                    placeholder="{{ __('public.language.search_placeholder') }}"
                    class="w-full rounded-lg border border-input bg-background py-2 pl-9 pr-3 text-sm placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    autocomplete="off"
                />
            </div>
        </div>
        @endif

        {{-- Languages list --}}
        <div class="max-h-64 overflow-y-auto py-1" x-ref="listContainer">
            @foreach($languages as $index => $lang)
                @php
                    $langFlag = $flags[$lang['code']] ?? '🌐';
                    $langCode = e($lang['code']);
                    $langNativeName = e($lang['native_name']);
                    $langName = e($lang['name'] ?? $lang['native_name']);
                @endphp
                <a 
                    href="{{ $lang['url'] }}"
                    x-show="matchesSearch('{{ $langCode }}', '{{ $langNativeName }}', '{{ $langName }}')"
                    :class="{
                        'bg-primary/10 text-primary': focusedIndex === {{ $index }},
                        'hover:bg-muted': focusedIndex !== {{ $index }}
                    }"
                    class="flex items-center justify-between px-3 py-2.5 text-sm transition {{ $lang['is_current'] ? 'bg-primary/5 font-semibold' : '' }}"
                    role="option"
                    :aria-selected="{{ $lang['is_current'] ? 'true' : 'false' }}"
                    data-index="{{ $index }}"
                    @mouseenter="focusedIndex = {{ $index }}"
                    @focus="focusedIndex = {{ $index }}"
                    tabindex="-1"
                >
                    <span class="flex items-center gap-2.5">
                        <span class="text-base">{{ $langFlag }}</span>
                        <span>{{ $lang['native_name'] }}</span>
                    </span>
                    <span class="flex items-center gap-2">
                        <span class="text-xs text-muted-foreground">{{ strtoupper($lang['code']) }}</span>
                        @if($lang['is_current'])
                            <svg class="h-4 w-4 text-primary" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        @endif
                    </span>
                </a>
            @endforeach
            
            {{-- No results message --}}
            <div 
                x-show="!hasVisibleItems()"
                class="px-3 py-4 text-center text-sm text-muted-foreground"
            >
                {{ __('public.language.no_results') }}
            </div>
        </div>
    </div>
</div>

<script>
function languageSwitcher() {
    return {
        open: false,
        searchQuery: '',
        focusedIndex: -1,
        languages: @json($languages),
        
        toggle() {
            this.open = !this.open;
            if (this.open) {
                this.searchQuery = '';
                this.focusedIndex = -1;
                this.$nextTick(() => {
                    if (this.$refs.searchInput) {
                        this.$refs.searchInput.focus();
                    }
                });
            }
        },
        
        matchesSearch(code, nativeName, name) {
            if (!this.searchQuery) return true;
            const query = this.searchQuery.toLowerCase();
            return code.toLowerCase().includes(query) ||
                   nativeName.toLowerCase().includes(query) ||
                   name.toLowerCase().includes(query);
        },
        
        hasVisibleItems() {
            return this.languages.some(lang => 
                this.matchesSearch(lang.code, lang.native_name, lang.name || lang.native_name)
            );
        },
        
        getVisibleItems() {
            return Array.from(this.$refs.listContainer.querySelectorAll('a'))
                .filter(el => el.offsetParent !== null);
        },
        
        focusFirst() {
            const items = this.getVisibleItems();
            if (items.length > 0) {
                this.focusedIndex = parseInt(items[0].dataset.index);
            }
        },
        
        focusNext() {
            const items = this.getVisibleItems();
            if (items.length === 0) return;
            
            const currentIdx = items.findIndex(el => parseInt(el.dataset.index) === this.focusedIndex);
            const nextIdx = currentIdx < items.length - 1 ? currentIdx + 1 : 0;
            this.focusedIndex = parseInt(items[nextIdx].dataset.index);
            items[nextIdx].scrollIntoView({ block: 'nearest' });
        },
        
        focusPrev() {
            const items = this.getVisibleItems();
            if (items.length === 0) return;
            
            const currentIdx = items.findIndex(el => parseInt(el.dataset.index) === this.focusedIndex);
            const prevIdx = currentIdx > 0 ? currentIdx - 1 : items.length - 1;
            this.focusedIndex = parseInt(items[prevIdx].dataset.index);
            items[prevIdx].scrollIntoView({ block: 'nearest' });
        },
        
        selectFocused() {
            const items = this.getVisibleItems();
            const focused = items.find(el => parseInt(el.dataset.index) === this.focusedIndex);
            if (focused) {
                window.location.href = focused.href;
            }
        }
    }
}
</script>
@endif
