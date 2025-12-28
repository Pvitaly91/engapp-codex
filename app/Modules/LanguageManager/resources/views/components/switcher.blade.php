{{-- Language Switcher Component --}}
{{-- Usage: @include('language-manager::components.switcher', ['style' => 'pills']) --}}
{{-- Styles: 'pills' (default), 'dropdown', 'dropdown-search' (for many languages), 'links', 'flags' --}}
{{-- Note: 'dropdown' and 'dropdown-search' styles require Alpine.js to be loaded --}}

@php
    $style = $style ?? 'pills';
    $languages = $__languageSwitcher ?? [];
    $currentLocale = $__currentLocale ?? app()->getLocale();
    $languageCount = count($languages);
@endphp

@if(count($languages) > 1)
    @if($style === 'pills')
        <div class="flex items-center gap-1 rounded-xl border border-border bg-muted/30 p-1">
            @foreach($languages as $lang)
                <a 
                    href="{{ $lang['url'] }}"
                    class="rounded-lg px-2.5 py-1 text-xs font-semibold transition {{ $lang['is_current'] ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:text-foreground hover:bg-background' }}"
                    @if($lang['is_current']) aria-current="true" @endif
                >
                    {{ strtoupper($lang['code']) }}
                </a>
            @endforeach
        </div>
    @elseif($style === 'dropdown-search')
        {{-- Enhanced dropdown with search for 20+ languages --}}
        <div class="relative" x-data="languageSwitcher({{ json_encode($languages) }}, '{{ $currentLocale }}')">
            <button 
                @click="open = !open"
                @click.away="open = false"
                type="button"
                class="flex items-center gap-2 rounded-xl border border-border bg-background px-3 py-2 text-sm font-medium transition hover:bg-muted min-w-[120px]"
                aria-label="{{ __('public.nav.change_language') }}"
            >
                <span class="flex-1 text-left" x-text="currentLanguage.native_name"></span>
                <svg class="h-4 w-4 transition" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div 
                x-show="open"
                x-transition
                class="absolute right-0 mt-2 w-64 rounded-xl border border-border bg-card shadow-lg z-50"
                @keydown.escape.window="open = false"
            >
                <div class="p-3 border-b border-border">
                    <input 
                        type="text"
                        x-model="searchQuery"
                        @input="filterLanguages"
                        placeholder="{{ __('public.nav.search_languages') }}"
                        class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
                        @keydown.down.prevent="focusNext"
                        @keydown.up.prevent="focusPrev"
                    />
                </div>
                <div class="max-h-64 overflow-y-auto py-1">
                    <template x-for="(lang, index) in filteredLanguages" :key="lang.code">
                        <a 
                            :href="lang.url"
                            class="flex items-center justify-between px-4 py-2.5 text-sm transition focus:bg-muted focus:outline-none"
                            :class="lang.is_current ? 'bg-primary/10 text-primary font-semibold' : 'text-foreground hover:bg-muted'"
                            @keydown.down.prevent="focusNext"
                            @keydown.up.prevent="focusPrev"
                        >
                            <span x-text="lang.native_name"></span>
                            <span class="text-xs text-muted-foreground" x-text="lang.code.toUpperCase()"></span>
                        </a>
                    </template>
                    <div x-show="filteredLanguages.length === 0" class="px-4 py-3 text-sm text-muted-foreground text-center">
                        {{ __('public.nav.no_languages_found') }}
                    </div>
                </div>
            </div>
        </div>
        <script>
        function languageSwitcher(languages, currentLocale) {
            const current = languages.find(l => l.code === currentLocale) || languages[0];
            return {
                open: false,
                searchQuery: '',
                languages: languages,
                filteredLanguages: languages,
                currentLanguage: current,
                filterLanguages() {
                    const query = this.searchQuery.toLowerCase();
                    this.filteredLanguages = this.languages.filter(lang => 
                        lang.native_name.toLowerCase().includes(query) ||
                        lang.name.toLowerCase().includes(query) ||
                        lang.code.toLowerCase().includes(query)
                    );
                },
                focusNext() {
                    const links = this.$el.querySelectorAll('a');
                    const current = document.activeElement;
                    const currentIndex = Array.from(links).indexOf(current);
                    if (currentIndex < links.length - 1) {
                        links[currentIndex + 1].focus();
                    }
                },
                focusPrev() {
                    const links = this.$el.querySelectorAll('a');
                    const current = document.activeElement;
                    const currentIndex = Array.from(links).indexOf(current);
                    if (currentIndex > 0) {
                        links[currentIndex - 1].focus();
                    } else {
                        this.$el.querySelector('input').focus();
                    }
                }
            }
        }
        </script>
    @elseif($style === 'dropdown')
        <div class="relative" x-data="{ open: false }">
            <button 
                @click="open = !open"
                @click.away="open = false"
                type="button"
                class="flex items-center gap-2 rounded-lg border border-border bg-background px-3 py-2 text-sm font-medium transition hover:bg-muted"
            >
                @foreach($languages as $lang)
                    @if($lang['is_current'])
                        <span>{{ $lang['native_name'] }}</span>
                    @endif
                @endforeach
                <svg class="h-4 w-4 transition" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div 
                x-show="open"
                x-transition
                class="absolute right-0 mt-2 w-48 rounded-xl border border-border bg-card py-1 shadow-lg z-50"
            >
                @foreach($languages as $lang)
                    <a 
                        href="{{ $lang['url'] }}"
                        class="flex items-center justify-between px-4 py-2 text-sm transition {{ $lang['is_current'] ? 'bg-primary/10 text-primary font-semibold' : 'text-foreground hover:bg-muted' }}"
                    >
                        <span>{{ $lang['native_name'] }}</span>
                        <span class="text-xs text-muted-foreground">{{ strtoupper($lang['code']) }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @elseif($style === 'links')
        <div class="flex items-center gap-3">
            @foreach($languages as $lang)
                <a 
                    href="{{ $lang['url'] }}"
                    class="text-sm font-medium transition {{ $lang['is_current'] ? 'text-primary' : 'text-muted-foreground hover:text-foreground' }}"
                    @if($lang['is_current']) aria-current="true" @endif
                >
                    {{ $lang['native_name'] }}
                </a>
                @unless($loop->last)
                    <span class="text-border">|</span>
                @endunless
            @endforeach
        </div>
    @elseif($style === 'flags')
        <div class="flex items-center gap-2">
            @foreach($languages as $lang)
                <a 
                    href="{{ $lang['url'] }}"
                    class="flex items-center gap-1.5 rounded-lg px-2 py-1 text-sm transition {{ $lang['is_current'] ? 'bg-primary/10 ring-2 ring-primary/30' : 'hover:bg-muted' }}"
                    @if($lang['is_current']) aria-current="true" @endif
                    title="{{ $lang['native_name'] }}"
                >
                    <span class="text-lg">
                        @switch($lang['code'])
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
                            @default
                                🌐
                        @endswitch
                    </span>
                    <span class="text-xs font-medium {{ $lang['is_current'] ? 'text-primary' : 'text-muted-foreground' }}">
                        {{ strtoupper($lang['code']) }}
                    </span>
                </a>
            @endforeach
        </div>
    @endif
@endif
