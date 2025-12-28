{{-- Public Footer Component --}}
<footer class="mt-auto border-t border-border bg-muted/30">
  <div class="page-container mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-8 sm:py-12">
      <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-4">
        
        {{-- Brand Column --}}
        <div class="lg:col-span-2">
          <div class="flex items-center gap-2.5 mb-4">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-primary to-primary/80 text-white shadow-sm">
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M5.5 8C5.5 5.23858 7.73858 3 10.5 3H13.25C16.1495 3 18.5 5.35051 18.5 8.25C18.5 11.1495 16.1495 13.5 13.25 13.5H11.5C9.567 13.5 8 15.067 8 17C8 18.933 9.567 20.5 11.5 20.5H16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M5 20.5H12.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
              </svg>
            </span>
            <span class="font-semibold text-foreground">Gramlyze</span>
          </div>
          <p class="text-sm text-muted-foreground max-w-md mb-4">
            {{ __('public.footer.description') }}
          </p>
          <div class="flex flex-wrap gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-background border border-border px-3 py-1 text-xs text-muted-foreground">
              🔒 {{ __('public.footer.data_security') }}
            </span>
            <span class="inline-flex items-center gap-1.5 rounded-full bg-background border border-border px-3 py-1 text-xs text-muted-foreground">
              🤝 {{ __('public.footer.team_support') }}
            </span>
          </div>
        </div>

        {{-- Navigation Column --}}
        <div>
          <h3 class="text-sm font-semibold text-foreground mb-3">{{ __('public.footer.navigation') }}</h3>
          <ul class="space-y-2">
            <li>
              <a href="{{ route('catalog.tests-cards') }}" class="text-sm text-muted-foreground hover:text-foreground transition-colors">
                {{ __('public.nav.catalog') }}
              </a>
            </li>
            <li>
              <a href="{{ route('theory.index') }}" class="text-sm text-muted-foreground hover:text-foreground transition-colors">
                {{ __('public.nav.theory') }}
              </a>
            </li>
            <li>
              <a href="{{ route('words.test') }}" class="text-sm text-muted-foreground hover:text-foreground transition-colors">
                {{ __('public.nav.words_test') }}
              </a>
            </li>
            <li>
              <a href="{{ route('verbs.test') }}" class="text-sm text-muted-foreground hover:text-foreground transition-colors">
                {{ __('public.nav.verbs_test') }}
              </a>
            </li>
          </ul>
        </div>

        {{-- Resources Column --}}
        <div>
          <h3 class="text-sm font-semibold text-foreground mb-3">{{ __('public.footer.resources') }}</h3>
          <ul class="space-y-2">
            <li>
              <a href="{{ route('search') }}" class="text-sm text-muted-foreground hover:text-foreground transition-colors">
                {{ __('public.search.title') }}
              </a>
            </li>
            <li>
              <a href="#" class="text-sm text-muted-foreground hover:text-foreground transition-colors">
                {{ __('public.footer.support') }}
              </a>
            </li>
            <li>
              <a href="#" class="text-sm text-muted-foreground hover:text-foreground transition-colors">
                {{ __('public.footer.policy') }}
              </a>
            </li>
            <li>
              <a href="#" class="text-sm text-muted-foreground hover:text-foreground transition-colors">
                {{ __('public.footer.terms') }}
              </a>
            </li>
          </ul>
        </div>
      </div>

      {{-- Bottom Bar --}}
      <div class="mt-8 pt-6 border-t border-border flex flex-col sm:flex-row items-center justify-between gap-4">
        <p class="text-xs text-muted-foreground">
          © <span id="footer-year"></span> Gramlyze. {{ __('public.footer.copyright') }}
        </p>
        
        <div class="flex items-center gap-3">
          {{-- Theme Toggle --}}
          <button 
            type="button"
            onclick="toggleTheme()"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-border text-xs font-medium text-muted-foreground hover:text-foreground hover:bg-muted/50 transition-colors"
            aria-label="{{ __('public.footer.theme') }}"
          >
            <svg class="h-4 w-4 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <svg class="h-4 w-4 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
            {{ __('public.footer.theme') }}
          </button>
          
          {{-- Admin Login Link --}}
          <a 
            href="{{ route('login.show') }}" 
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-border text-xs font-medium text-muted-foreground hover:text-foreground hover:bg-muted/50 transition-colors"
          >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            {{ __('public.footer.admin_login') }}
          </a>
        </div>
      </div>
    </div>
  </div>
</footer>

<script>
  document.getElementById('footer-year').textContent = new Date().getFullYear();
</script>
