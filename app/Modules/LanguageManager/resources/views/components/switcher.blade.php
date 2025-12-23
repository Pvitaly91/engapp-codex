{{-- Language Switcher Component --}}
{{-- Usage: @include('language-manager::components.switcher', ['style' => 'pills']) --}}
{{-- Styles: 'pills' (default), 'dropdown', 'links', 'flags' --}}
{{-- Note: 'dropdown' style requires Alpine.js to be loaded --}}

@php
    $style = $style ?? 'pills';
    $languages = $__languageSwitcher ?? [];
    $currentLocale = $__currentLocale ?? app()->getLocale();
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
