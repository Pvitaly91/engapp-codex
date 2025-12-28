{{-- 
  Advanced Language Switcher Component V2
  Handles many languages with search/filter capability
  Usage: @include('components.language-switcher-v2')
--}}

@php
    $languages = $__languages ?? collect();
    $currentLocale = $__currentLocale ?? app()->getLocale();
    $currentLang = $languages->firstWhere('code', $currentLocale);
@endphp

@if($languages->count() > 0)
<div 
  x-data="{ 
    open: false,
    search: '',
    get filteredLanguages() {
      if (!this.search) return @js($languages->toArray());
      const query = this.search.toLowerCase();
      return @js($languages->toArray()).filter(lang => 
        lang.code.toLowerCase().includes(query) || 
        lang.native_name.toLowerCase().includes(query) ||
        (lang.name && lang.name.toLowerCase().includes(query))
      );
    }
  }"
  @keydown.escape.window="open = false"
  class="relative"
>
  <!-- Trigger Button -->
  <button 
    @click="open = !open"
    type="button"
    class="flex items-center gap-2 px-3 py-2 rounded-xl border border-border bg-background hover:bg-muted transition-colors text-sm"
    aria-label="{{ __('public.language.switch') }}"
    :aria-expanded="open"
  >
    <!-- Current Language Flag/Icon -->
    <span class="text-base">
      @if($currentLang && isset($currentLang['code']))
        @switch($currentLang['code'])
          @case('uk')
            🇺🇦
            @break
          @case('en')
            🇬🇧
            @break
          @case('pl')
            🇵🇱
            @break
          @case('de')
            🇩🇪
            @break
          @case('fr')
            🇫🇷
            @break
          @case('es')
            🇪🇸
            @break
          @case('it')
            🇮🇹
            @break
          @case('pt')
            🇵🇹
            @break
          @case('ru')
            🇷🇺
            @break
          @case('ja')
            🇯🇵
            @break
          @case('zh')
            🇨🇳
            @break
          @case('ko')
            🇰🇷
            @break
          @case('ar')
            🇸🇦
            @break
          @case('hi')
            🇮🇳
            @break
          @case('tr')
            🇹🇷
            @break
          @default
            🌐
        @endswitch
      @else
        🌐
      @endif
    </span>
    
    <!-- Current Language Code -->
    <span class="font-semibold text-foreground">
      {{ strtoupper($currentLang['code'] ?? $currentLocale) }}
    </span>
    
    <!-- Dropdown Arrow -->
    <svg 
      class="w-4 h-4 transition-transform" 
      :class="{ 'rotate-180': open }"
      fill="none" 
      stroke="currentColor" 
      viewBox="0 0 24 24"
    >
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
    </svg>
  </button>
  
  <!-- Dropdown Panel -->
  <div 
    x-show="open"
    @click.away="open = false"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 scale-95"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
    class="absolute right-0 mt-2 w-72 sm:w-80 rounded-2xl border border-border bg-card shadow-soft-lg z-50"
    style="display: none;"
  >
    <!-- Header with Search -->
    <div class="p-4 border-b border-border">
      <h3 class="text-sm font-semibold text-foreground mb-3">
        {{ __('public.language.select') }}
      </h3>
      
      @if($languages->count() > 5)
      <!-- Search Input (only show if more than 5 languages) -->
      <div class="relative">
        <input 
          x-model="search"
          type="text"
          placeholder="{{ __('public.language.search_placeholder') }}"
          class="w-full pl-9 pr-3 py-2 rounded-lg border border-border bg-background text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 transition-colors"
          @click.stop
        />
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>
      @endif
    </div>
    
    <!-- Language List with Scroll -->
    <div class="max-h-64 overflow-y-auto hide-scrollbar">
      <template x-for="lang in filteredLanguages" :key="lang.code">
        <a 
          :href="lang.url"
          class="flex items-center justify-between px-4 py-3 hover:bg-muted transition-colors group"
          :class="{ 'bg-primary/5': lang.is_current }"
        >
          <div class="flex items-center gap-3">
            <!-- Flag -->
            <span class="text-xl" x-text="
              lang.code === 'uk' ? '🇺🇦' :
              lang.code === 'en' ? '🇬🇧' :
              lang.code === 'pl' ? '🇵🇱' :
              lang.code === 'de' ? '🇩🇪' :
              lang.code === 'fr' ? '🇫🇷' :
              lang.code === 'es' ? '🇪🇸' :
              lang.code === 'it' ? '🇮🇹' :
              lang.code === 'pt' ? '🇵🇹' :
              lang.code === 'ru' ? '🇷🇺' :
              lang.code === 'ja' ? '🇯🇵' :
              lang.code === 'zh' ? '🇨🇳' :
              lang.code === 'ko' ? '🇰🇷' :
              lang.code === 'ar' ? '🇸🇦' :
              lang.code === 'hi' ? '🇮🇳' :
              lang.code === 'tr' ? '🇹🇷' :
              '🌐'
            "></span>
            
            <!-- Language Name -->
            <div class="flex flex-col">
              <span 
                class="text-sm font-medium text-foreground group-hover:text-primary transition-colors"
                :class="{ 'text-primary': lang.is_current }"
                x-text="lang.native_name"
              ></span>
              <span 
                class="text-xs text-muted-foreground"
                x-text="lang.name || ''"
                x-show="lang.name"
              ></span>
            </div>
          </div>
          
          <!-- Code & Current Indicator -->
          <div class="flex items-center gap-2">
            <span 
              class="text-xs font-semibold text-muted-foreground"
              x-text="lang.code.toUpperCase()"
            ></span>
            <svg 
              x-show="lang.is_current"
              class="w-5 h-5 text-primary"
              fill="none" 
              stroke="currentColor" 
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
          </div>
        </a>
      </template>
      
      <!-- No Results Message -->
      <div 
        x-show="filteredLanguages.length === 0"
        class="px-4 py-8 text-center text-sm text-muted-foreground"
      >
        {{ __('public.language.no_results') }}
      </div>
    </div>
  </div>
</div>
@endif
