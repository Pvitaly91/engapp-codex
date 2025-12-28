{{--
  Advanced Search Modal Component V2
  Includes:
  - Page/Test search with autocomplete
  - Dictionary word search with translations
--}}

<div 
  x-data="{
    open: false,
    activeTab: 'pages',
    pageQuery: '',
    wordQuery: '',
    pageResults: [],
    wordResults: [],
    pageLoading: false,
    wordLoading: false,
    pageController: null,
    wordController: null,
    wordDebounceTimer: null,
    
    init() {
      this.$watch('open', value => {
        if (value) {
          this.$nextTick(() => {
            if (this.activeTab === 'pages') {
              this.$refs.pageSearchInput?.focus();
            } else {
              this.$refs.wordSearchInput?.focus();
            }
          });
        }
      });
    },
    
    async searchPages() {
      const query = this.pageQuery.trim();
      if (query.length < 2) {
        this.pageResults = [];
        return;
      }
      
      this.pageController?.abort();
      this.pageController = new AbortController();
      this.pageLoading = true;
      
      try {
        const res = await fetch('{{ route('site.search') }}?q=' + encodeURIComponent(query), {
          headers: { 'Accept': 'application/json' },
          signal: this.pageController.signal
        });
        const data = await res.json();
        this.pageResults = data;
      } catch (e) {
        if (e.name !== 'AbortError') {
          console.error('Search error:', e);
        }
      } finally {
        this.pageLoading = false;
      }
    },
    
    async searchWords() {
      const query = this.wordQuery.trim();
      if (query.length < 2) {
        this.wordResults = [];
        return;
      }
      
      clearTimeout(this.wordDebounceTimer);
      this.wordDebounceTimer = setTimeout(async () => {
        this.wordController?.abort();
        this.wordController = new AbortController();
        this.wordLoading = true;
        
        try {
          const currentLang = '{{ app()->getLocale() }}';
          const res = await fetch('/api/search?lang=' + currentLang + '&q=' + encodeURIComponent(query), {
            signal: this.wordController.signal
          });
          const data = await res.json();
          this.wordResults = data;
        } catch (e) {
          if (e.name !== 'AbortError') {
            console.error('Word search error:', e);
          }
        } finally {
          this.wordLoading = false;
        }
      }, 300); // 300ms debounce
    },
    
    switchTab(tab) {
      this.activeTab = tab;
      this.$nextTick(() => {
        if (tab === 'pages') {
          this.$refs.pageSearchInput?.focus();
        } else {
          this.$refs.wordSearchInput?.focus();
        }
      });
    },
    
    submitPageSearch() {
      if (this.pageQuery.trim()) {
        window.location.href = '{{ route('site.search') }}?q=' + encodeURIComponent(this.pageQuery.trim());
      }
    }
  }"
  @open-search-modal.window="open = true"
  @keydown.escape.window="open = false"
  class="relative z-50"
>
  <!-- Backdrop -->
  <div 
    x-show="open"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    @click="open = false"
    class="fixed inset-0 bg-foreground/50 backdrop-blur-sm"
    style="display: none;"
  ></div>
  
  <!-- Modal Panel -->
  <div 
    x-show="open"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 scale-95 -translate-y-4"
    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
    x-transition:leave-end="opacity-0 scale-95 -translate-y-4"
    class="fixed inset-x-4 top-20 sm:inset-x-auto sm:left-1/2 sm:-translate-x-1/2 sm:w-full sm:max-w-2xl"
    style="display: none;"
  >
    <div class="rounded-2xl border border-border bg-card shadow-soft-lg overflow-hidden">
      
      <!-- Header with Tabs -->
      <div class="border-b border-border bg-muted/30">
        <div class="flex items-center justify-between p-4 pb-0">
          <div class="flex gap-1 flex-1">
            <button 
              @click="switchTab('pages')"
              class="px-4 py-2 rounded-t-lg text-sm font-medium transition-colors"
              :class="activeTab === 'pages' ? 'bg-card text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'"
            >
              {{ __('public.search.pages_tests') }}
            </button>
            <button 
              @click="switchTab('words')"
              class="px-4 py-2 rounded-t-lg text-sm font-medium transition-colors"
              :class="activeTab === 'words' ? 'bg-card text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'"
            >
              {{ __('public.search.dictionary') }}
            </button>
          </div>
          
          <!-- Close Button -->
          <button 
            @click="open = false"
            class="p-2 rounded-lg hover:bg-muted transition-colors -mr-2"
            aria-label="{{ __('public.common.close') }}"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>
      
      <!-- Search Content -->
      <div class="p-4">
        
        <!-- Pages/Tests Search Tab -->
        <div x-show="activeTab === 'pages'" class="space-y-4">
          <form @submit.prevent="submitPageSearch" class="relative">
            <input 
              x-ref="pageSearchInput"
              x-model="pageQuery"
              @input.debounce.300ms="searchPages()"
              type="text"
              placeholder="{{ __('public.search.placeholder') }}"
              class="w-full pl-11 pr-4 py-3 rounded-xl border border-border bg-background text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 transition-colors"
            />
            <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            
            <!-- Loading Spinner -->
            <div x-show="pageLoading" class="absolute right-4 top-1/2 -translate-y-1/2">
              <svg class="animate-spin w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </div>
          </form>
          
          <!-- Results -->
          <div class="max-h-96 overflow-y-auto hide-scrollbar">
            <template x-if="pageResults.length > 0">
              <div class="space-y-2">
                <template x-for="result in pageResults" :key="result.url">
                  <a 
                    :href="result.url"
                    class="flex items-center justify-between p-3 rounded-xl hover:bg-muted transition-colors group"
                  >
                    <div class="flex-1 min-w-0">
                      <p class="text-sm font-medium text-foreground group-hover:text-primary transition-colors truncate" x-text="result.title"></p>
                      <p class="text-xs text-muted-foreground mt-0.5">
                        <span x-text="result.type === 'page' ? '{{ __('public.common.type_theory') }}' : '{{ __('public.common.type_test') }}'"></span>
                      </p>
                    </div>
                    <svg class="w-5 h-5 text-muted-foreground group-hover:text-primary group-hover:translate-x-1 transition-all flex-shrink-0 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                  </a>
                </template>
              </div>
            </template>
            
            <template x-if="pageQuery.length >= 2 && pageResults.length === 0 && !pageLoading">
              <div class="py-12 text-center">
                <svg class="w-12 h-12 mx-auto text-muted-foreground/50 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <p class="text-sm text-muted-foreground">{{ __('public.search.nothing_found') }}</p>
              </div>
            </template>
            
            <template x-if="pageQuery.length < 2">
              <div class="py-12 text-center">
                <svg class="w-12 h-12 mx-auto text-muted-foreground/50 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <p class="text-sm text-muted-foreground">{{ __('public.search.start_typing') }}</p>
              </div>
            </template>
          </div>
        </div>
        
        <!-- Dictionary Search Tab -->
        <div x-show="activeTab === 'words'" class="space-y-4">
          <div class="relative">
            <input 
              x-ref="wordSearchInput"
              x-model="wordQuery"
              @input="searchWords()"
              type="text"
              placeholder="{{ __('public.search.word_placeholder') }}"
              class="w-full pl-11 pr-4 py-3 rounded-xl border border-border bg-background text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 transition-colors"
            />
            <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
            
            <!-- Loading Spinner -->
            <div x-show="wordLoading" class="absolute right-4 top-1/2 -translate-y-1/2">
              <svg class="animate-spin w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </div>
          </div>
          
          <!-- Results -->
          <div class="max-h-96 overflow-y-auto hide-scrollbar">
            <template x-if="wordResults.length > 0">
              <div class="space-y-2">
                <template x-for="word in wordResults" :key="word.en">
                  <div class="p-3 rounded-xl bg-muted/30 hover:bg-muted transition-colors">
                    <div class="flex items-start justify-between gap-3">
                      <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-foreground" x-text="word.en"></p>
                        <p 
                          class="text-sm text-muted-foreground mt-1"
                          x-text="word.translation || '{{ __('public.search.no_translation') }}'"
                          :class="{ 'italic opacity-60': !word.translation }"
                        ></p>
                      </div>
                      <svg class="w-5 h-5 text-primary flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                      </svg>
                    </div>
                  </div>
                </template>
              </div>
            </template>
            
            <template x-if="wordQuery.length >= 2 && wordResults.length === 0 && !wordLoading">
              <div class="py-12 text-center">
                <svg class="w-12 h-12 mx-auto text-muted-foreground/50 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <p class="text-sm text-muted-foreground">{{ __('public.search.no_words_found') }}</p>
              </div>
            </template>
            
            <template x-if="wordQuery.length < 2">
              <div class="py-12 text-center">
                <svg class="w-12 h-12 mx-auto text-muted-foreground/50 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <p class="text-sm text-muted-foreground">{{ __('public.search.start_typing_word') }}</p>
              </div>
            </template>
          </div>
        </div>
      </div>
      
      <!-- Footer Tips -->
      <div class="border-t border-border bg-muted/30 px-4 py-3">
        <div class="flex items-center gap-4 text-xs text-muted-foreground">
          <div class="flex items-center gap-1.5">
            <kbd class="px-2 py-0.5 rounded bg-background border border-border font-mono">ESC</kbd>
            <span>{{ __('public.common.close') }}</span>
          </div>
          <div class="flex items-center gap-1.5">
            <kbd class="px-2 py-0.5 rounded bg-background border border-border font-mono">↵</kbd>
            <span x-show="activeTab === 'pages'">{{ __('public.search.view_all') }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
