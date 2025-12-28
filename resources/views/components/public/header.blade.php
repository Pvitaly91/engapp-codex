{{-- Public Header Component --}}
@php
    $languages = $__languageSwitcher ?? [];
    $currentLocale = $__currentLocale ?? app()->getLocale();
@endphp

<header class="sticky top-0 z-50 w-full border-b border-border/40 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/80">
  <div class="page-container mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex h-14 sm:h-16 items-center justify-between gap-4">
      
      {{-- Logo --}}
      <a href="{{ route('home') }}" class="flex items-center gap-2.5 flex-shrink-0 group" aria-label="Gramlyze">
        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-primary to-primary/80 text-white shadow-sm transition-transform group-hover:scale-105">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M5.5 8C5.5 5.23858 7.73858 3 10.5 3H13.25C16.1495 3 18.5 5.35051 18.5 8.25C18.5 11.1495 16.1495 13.5 13.25 13.5H11.5C9.567 13.5 8 15.067 8 17C8 18.933 9.567 20.5 11.5 20.5H16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M5 20.5H12.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
          </svg>
        </span>
        <span class="hidden sm:block font-semibold text-foreground">Gramlyze</span>
      </a>

      {{-- Desktop Navigation --}}
      <nav class="hidden lg:flex items-center gap-1">
        <a href="{{ route('catalog.tests-cards') }}" class="px-3 py-2 text-sm font-medium text-muted-foreground rounded-lg transition-colors hover:text-foreground hover:bg-muted/50">
          {{ __('public.nav.catalog') }}
        </a>
        <a href="{{ route('theory.index') }}" class="px-3 py-2 text-sm font-medium text-muted-foreground rounded-lg transition-colors hover:text-foreground hover:bg-muted/50">
          {{ __('public.nav.theory') }}
        </a>
        <a href="{{ route('words.test') }}" class="px-3 py-2 text-sm font-medium text-muted-foreground rounded-lg transition-colors hover:text-foreground hover:bg-muted/50">
          {{ __('public.nav.words_test') }}
        </a>
        <a href="{{ route('verbs.test') }}" class="px-3 py-2 text-sm font-medium text-muted-foreground rounded-lg transition-colors hover:text-foreground hover:bg-muted/50">
          {{ __('public.nav.verbs_test') }}
        </a>
      </nav>

      {{-- Search, Language Switcher, Mobile Menu --}}
      <div class="flex items-center gap-2 sm:gap-3">
        
        {{-- Page Search (Desktop) --}}
        <div class="hidden md:block" x-data="pageSearch()">
          <div class="relative">
            <form action="{{ route('search') }}" method="GET" class="relative">
              <input 
                type="search" 
                name="q" 
                x-model="query"
                @input.debounce.300ms="search"
                @focus="showResults = query.length >= 2"
                @click.away="showResults = false"
                autocomplete="off"
                placeholder="{{ __('public.search.placeholder') }}" 
                class="w-48 lg:w-56 h-9 rounded-lg border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-ring"
                aria-label="{{ __('public.search.placeholder') }}"
              />
              <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground" aria-label="{{ __('public.search.button') }}">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </button>
            </form>
            
            {{-- Search Results Dropdown --}}
            <div 
              x-show="showResults && results.length > 0"
              x-cloak
              class="absolute top-full left-0 right-0 mt-1 bg-popover border border-border rounded-lg shadow-lg overflow-hidden z-50 animate-fade-in"
            >
              <ul class="py-1 max-h-64 overflow-auto scrollbar-thin" role="listbox">
                <template x-for="item in results" :key="item.url">
                  <li>
                    <a 
                      :href="item.url" 
                      class="flex items-center justify-between px-3 py-2 text-sm hover:bg-muted transition-colors"
                      @click="showResults = false"
                    >
                      <span x-text="item.title" class="truncate"></span>
                      <span 
                        x-text="item.type === 'page' ? '{{ __('public.common.type_theory') }}' : '{{ __('public.common.type_test') }}'"
                        class="flex-shrink-0 ml-2 text-xs text-muted-foreground bg-muted px-1.5 py-0.5 rounded"
                      ></span>
                    </a>
                  </li>
                </template>
              </ul>
            </div>
          </div>
        </div>
        
        {{-- Dictionary Search (Desktop) --}}
        <div class="hidden lg:block" x-data="dictionarySearch()">
          <div class="relative">
            <button 
              @click="open = !open"
              class="flex items-center gap-1.5 h-9 px-3 rounded-lg border border-input bg-background text-sm font-medium text-muted-foreground hover:text-foreground hover:bg-muted/50 transition-colors"
              :aria-expanded="open"
              aria-haspopup="true"
            >
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
              </svg>
              <span class="hidden xl:inline">{{ __('public.dictionary.title') }}</span>
            </button>
            
            {{-- Dictionary Dropdown --}}
            <div 
              x-show="open"
              x-cloak
              @click.away="open = false"
              class="absolute top-full right-0 mt-1 w-80 bg-popover border border-border rounded-lg shadow-lg z-50 animate-fade-in"
            >
              <div class="p-3">
                <label class="text-xs font-medium text-muted-foreground mb-1.5 block">{{ __('public.dictionary.search_word') }}</label>
                <input 
                  type="text"
                  x-model="query"
                  @input.debounce.300ms="searchWord"
                  placeholder="{{ __('public.dictionary.placeholder') }}"
                  class="w-full h-9 rounded-lg border border-input bg-background px-3 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-ring"
                  autocomplete="off"
                />
              </div>
              
              <div x-show="loading" class="px-3 py-4 text-center">
                <svg class="animate-spin h-5 w-5 mx-auto text-muted-foreground" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
              </div>
              
              <ul x-show="!loading && results.length > 0" class="py-1 max-h-64 overflow-auto scrollbar-thin border-t border-border">
                <template x-for="word in results" :key="word.en">
                  <li class="px-3 py-2 hover:bg-muted/50 transition-colors">
                    <div class="flex items-start justify-between gap-2">
                      <span class="font-medium text-foreground" x-text="word.en"></span>
                      <span 
                        class="text-sm text-muted-foreground"
                        x-text="word.translation || '{{ __('public.dictionary.no_translation') }}'"
                      ></span>
                    </div>
                  </li>
                </template>
              </ul>
              
              <div x-show="!loading && query.length >= 2 && results.length === 0" class="px-3 py-4 text-center text-sm text-muted-foreground border-t border-border">
                {{ __('public.dictionary.not_found') }}
              </div>
            </div>
          </div>
        </div>

        {{-- Language Switcher --}}
        @if(count($languages) > 1)
        <div x-data="{ open: false, filter: '' }" class="relative">
          <button 
            @click="open = !open; if(open) { $nextTick(() => $refs.langFilter?.focus()) }"
            class="flex items-center gap-1.5 h-9 px-2.5 rounded-lg border border-input bg-background text-sm font-medium text-foreground hover:bg-muted/50 transition-colors"
            :aria-expanded="open"
            aria-haspopup="listbox"
            aria-label="{{ __('public.language.switch') }}"
          >
            <svg class="h-4 w-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            @foreach($languages as $lang)
              @if($lang['is_current'])
                <span>{{ strtoupper($lang['code']) }}</span>
              @endif
            @endforeach
            <svg class="h-3.5 w-3.5 text-muted-foreground transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>

          {{-- Language Dropdown --}}
          <div 
            x-show="open"
            x-cloak
            @click.away="open = false; filter = ''"
            class="absolute top-full right-0 mt-1 w-64 bg-popover border border-border rounded-lg shadow-lg z-50 animate-fade-in"
            role="listbox"
            aria-label="{{ __('public.language.select') }}"
          >
            {{-- Search Filter --}}
            @if(count($languages) > 5)
            <div class="p-2 border-b border-border">
              <input 
                type="text"
                x-ref="langFilter"
                x-model="filter"
                placeholder="{{ __('public.language.filter') }}"
                class="w-full h-8 rounded-md border border-input bg-background px-2.5 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-ring"
              />
            </div>
            @endif
            
            {{-- Languages List --}}
            <ul class="py-1 max-h-64 overflow-auto scrollbar-thin">
              @foreach($languages as $lang)
              <li 
                x-show="filter === '' || '{{ strtolower($lang['native_name']) }}'.includes(filter.toLowerCase()) || '{{ strtolower($lang['code']) }}'.includes(filter.toLowerCase())"
              >
                <a 
                  href="{{ $lang['url'] }}"
                  class="flex items-center justify-between px-3 py-2 text-sm transition-colors {{ $lang['is_current'] ? 'bg-primary/10 text-primary font-medium' : 'text-foreground hover:bg-muted/50' }}"
                  role="option"
                  @if($lang['is_current']) aria-selected="true" @endif
                >
                  <span class="flex items-center gap-2">
                    <span class="text-base">
                      @switch($lang['code'])
                        @case('uk') 🇺🇦 @break
                        @case('en') 🇬🇧 @break
                        @case('pl') 🇵🇱 @break
                        @case('de') 🇩🇪 @break
                        @case('fr') 🇫🇷 @break
                        @case('es') 🇪🇸 @break
                        @case('it') 🇮🇹 @break
                        @case('pt') 🇵🇹 @break
                        @case('nl') 🇳🇱 @break
                        @case('cs') 🇨🇿 @break
                        @case('sk') 🇸🇰 @break
                        @case('hu') 🇭🇺 @break
                        @case('ro') 🇷🇴 @break
                        @case('bg') 🇧🇬 @break
                        @case('hr') 🇭🇷 @break
                        @case('sl') 🇸🇮 @break
                        @case('lt') 🇱🇹 @break
                        @case('lv') 🇱🇻 @break
                        @case('et') 🇪🇪 @break
                        @default 🌐
                      @endswitch
                    </span>
                    <span class="truncate">{{ $lang['native_name'] }}</span>
                  </span>
                  <span class="text-xs text-muted-foreground uppercase">{{ $lang['code'] }}</span>
                </a>
              </li>
              @endforeach
            </ul>
          </div>
        </div>
        @endif

        {{-- Mobile Menu Button --}}
        <button 
          type="button"
          class="lg:hidden flex items-center justify-center h-9 w-9 rounded-lg border border-input hover:bg-muted/50 transition-colors"
          @click="$dispatch('toggle-mobile-menu')"
          aria-label="{{ __('public.nav.menu') }}"
          aria-expanded="false"
          aria-controls="mobile-menu"
        >
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
      </div>
    </div>
  </div>
  
  {{-- Mobile Menu --}}
  <div 
    x-data="{ open: false }"
    @toggle-mobile-menu.window="open = !open"
    x-show="open"
    x-cloak
    class="lg:hidden border-t border-border bg-background"
    id="mobile-menu"
  >
    <div class="page-container mx-auto px-4 py-4 space-y-4">
      {{-- Mobile Search --}}
      <form action="{{ route('search') }}" method="GET" class="relative">
        <input 
          type="search" 
          name="q" 
          placeholder="{{ __('public.search.placeholder') }}" 
          class="w-full h-10 rounded-lg border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-ring"
        />
        <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground">
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </button>
      </form>
      
      {{-- Mobile Navigation Links --}}
      <nav class="flex flex-col gap-1">
        <a href="{{ route('catalog.tests-cards') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-foreground rounded-lg hover:bg-muted/50 transition-colors">
          <svg class="h-5 w-5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
          </svg>
          {{ __('public.nav.catalog') }}
        </a>
        <a href="{{ route('theory.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-foreground rounded-lg hover:bg-muted/50 transition-colors">
          <svg class="h-5 w-5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
          </svg>
          {{ __('public.nav.theory') }}
        </a>
        <a href="{{ route('words.test') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-foreground rounded-lg hover:bg-muted/50 transition-colors">
          <svg class="h-5 w-5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
          </svg>
          {{ __('public.nav.words_test') }}
        </a>
        <a href="{{ route('verbs.test') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-foreground rounded-lg hover:bg-muted/50 transition-colors">
          <svg class="h-5 w-5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
          </svg>
          {{ __('public.nav.verbs_test') }}
        </a>
      </nav>
      
      {{-- Mobile Dictionary Search --}}
      <div x-data="dictionarySearch()" class="border-t border-border pt-4">
        <label class="text-xs font-medium text-muted-foreground mb-1.5 block">{{ __('public.dictionary.title') }}</label>
        <input 
          type="text"
          x-model="query"
          @input.debounce.300ms="searchWord"
          placeholder="{{ __('public.dictionary.placeholder') }}"
          class="w-full h-10 rounded-lg border border-input bg-background px-3 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-ring"
        />
        
        <div x-show="loading" class="mt-3 text-center">
          <svg class="animate-spin h-5 w-5 mx-auto text-muted-foreground" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </div>
        
        <ul x-show="!loading && results.length > 0" class="mt-2 rounded-lg border border-border divide-y divide-border">
          <template x-for="word in results" :key="word.en">
            <li class="px-3 py-2">
              <div class="flex items-start justify-between gap-2">
                <span class="font-medium text-foreground" x-text="word.en"></span>
                <span 
                  class="text-sm text-muted-foreground"
                  x-text="word.translation || '{{ __('public.dictionary.no_translation') }}'"
                ></span>
              </div>
            </li>
          </template>
        </ul>
      </div>
    </div>
  </div>
</header>

<script>
  function pageSearch() {
    return {
      query: '',
      results: [],
      showResults: false,
      
      async search() {
        if (this.query.length < 2) {
          this.results = [];
          this.showResults = false;
          return;
        }
        
        try {
          const response = await fetch(`{{ route('search') }}?q=${encodeURIComponent(this.query)}`, {
            headers: { 'Accept': 'application/json' }
          });
          this.results = await response.json();
          this.showResults = true;
        } catch (e) {
          this.results = [];
        }
      }
    }
  }
  
  function dictionarySearch() {
    return {
      query: '',
      results: [],
      loading: false,
      open: false,
      
      async searchWord() {
        if (this.query.length < 2) {
          this.results = [];
          return;
        }
        
        this.loading = true;
        
        try {
          const locale = '{{ $currentLocale }}';
          const response = await fetch(`{{ url('/api/search') }}?lang=${locale}&q=${encodeURIComponent(this.query)}`);
          this.results = await response.json();
        } catch (e) {
          this.results = [];
        } finally {
          this.loading = false;
        }
      }
    }
  }
</script>
