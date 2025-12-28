<!doctype html>
<html lang="{{ app()->getLocale() }}" class="h-full">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>@yield('title', __('public.meta.title'))</title>
  <meta name="description" content="{{ __('public.meta.description') }}" />

  <!-- Google Font: Inter for modern clean look -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Tailwind CDN + runtime config -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="//unpkg.com/alpinejs" defer></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
          colors: {
            // Modern gradient-friendly color system
            border: 'hsl(var(--border))',
            input: 'hsl(var(--input))',
            ring: 'hsl(var(--ring))',
            background: 'hsl(var(--background))',
            foreground: 'hsl(var(--foreground))',
            primary: { 
              DEFAULT: 'hsl(var(--primary))', 
              foreground: 'hsl(var(--primary-foreground))',
              50: 'hsl(var(--primary-50))',
              100: 'hsl(var(--primary-100))',
              200: 'hsl(var(--primary-200))',
            },
            secondary: { 
              DEFAULT: 'hsl(var(--secondary))', 
              foreground: 'hsl(var(--secondary-foreground))' 
            },
            muted: { 
              DEFAULT: 'hsl(var(--muted))', 
              foreground: 'hsl(var(--muted-foreground))' 
            },
            accent: { 
              DEFAULT: 'hsl(var(--accent))', 
              foreground: 'hsl(var(--accent-foreground))' 
            },
            card: { 
              DEFAULT: 'hsl(var(--card))', 
              foreground: 'hsl(var(--card-foreground))' 
            },
            success: 'hsl(var(--success))',
            warning: 'hsl(var(--warning))',
            destructive: { 
              DEFAULT: 'hsl(var(--destructive))', 
              foreground: 'hsl(var(--destructive-foreground))' 
            },
            info: 'hsl(var(--info))',
          },
          borderRadius: { 
            xl: '0.875rem', 
            '2xl': '1.25rem', 
            '3xl': '1.75rem' 
          },
          boxShadow: { 
            'soft': '0 2px 15px -3px rgba(0,0,0,0.07), 0 10px 20px -2px rgba(0,0,0,0.04)',
            'soft-lg': '0 10px 40px -10px rgba(0,0,0,0.1), 0 20px 30px -5px rgba(0,0,0,0.06)'
          }
        }
      }
    }
  </script>

  <!-- Design tokens (light/dark) - New vibrant system -->
  <style>
    :root {
      /* Clean neutral surfaces */
      --background: 220 18% 97%;
      --foreground: 220 25% 12%;
      --card: 0 0% 100%;
      --card-foreground: 220 25% 12%;
      
      /* Modern brand colors - vibrant yet professional */
      --primary: 262 83% 58%;
      --primary-foreground: 0 0% 100%;
      --primary-50: 262 83% 98%;
      --primary-100: 262 83% 95%;
      --primary-200: 262 83% 90%;
      
      --secondary: 200 98% 39%;
      --secondary-foreground: 0 0% 100%;
      
      --muted: 220 18% 94%;
      --muted-foreground: 220 15% 45%;
      
      --accent: 24 95% 53%;
      --accent-foreground: 0 0% 100%;
      
      --destructive: 0 72% 51%;
      --destructive-foreground: 0 0% 100%;
      
      --success: 142 71% 45%;
      --warning: 38 92% 50%;
      --info: 217 91% 60%;
      
      --border: 220 18% 88%;
      --input: 220 18% 88%;
      --ring: 262 83% 58%;
    }
    
    .dark {
      --background: 224 20% 8%;
      --foreground: 0 0% 98%;
      --card: 224 20% 11%;
      --card-foreground: 0 0% 98%;
      
      --primary: 262 83% 65%;
      --primary-foreground: 224 20% 8%;
      --primary-50: 262 83% 12%;
      --primary-100: 262 83% 15%;
      --primary-200: 262 83% 20%;
      
      --secondary: 200 98% 45%;
      --secondary-foreground: 224 20% 8%;
      
      --muted: 224 20% 15%;
      --muted-foreground: 0 0% 70%;
      
      --accent: 24 95% 58%;
      --accent-foreground: 224 20% 8%;
      
      --destructive: 0 62% 50%;
      --destructive-foreground: 0 0% 100%;
      
      --success: 142 60% 50%;
      --warning: 38 88% 55%;
      --info: 217 80% 65%;
      
      --border: 224 20% 20%;
      --input: 224 20% 20%;
      --ring: 262 83% 58%;
    }
    
    html, body { 
      height: 100%;
      scroll-behavior: smooth;
    }
    
    body { 
      background: linear-gradient(135deg, hsl(var(--background)) 0%, hsl(var(--muted)) 100%);
      color: hsl(var(--foreground));
      font-feature-settings: 'cv11', 'ss01';
    }
    
    .page-container { 
      max-width: 80rem;
      margin: 0 auto;
    }
    
    /* Smooth animations */
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    [data-animate] {
      animation: fadeInUp 0.6s ease-out;
      animation-fill-mode: both;
      animation-delay: var(--animate-delay, 0s);
    }
    
    /* Custom scrollbar */
    ::-webkit-scrollbar {
      width: 8px;
      height: 8px;
    }
    
    ::-webkit-scrollbar-track {
      background: hsl(var(--muted));
    }
    
    ::-webkit-scrollbar-thumb {
      background: hsl(var(--border));
      border-radius: 4px;
    }
    
    ::-webkit-scrollbar-thumb:hover {
      background: hsl(var(--muted-foreground));
    }
    
    /* Hide scrollbar for language list but keep functionality */
    .hide-scrollbar::-webkit-scrollbar {
      display: none;
    }
    
    .hide-scrollbar {
      -ms-overflow-style: none;
      scrollbar-width: none;
    }
  </style>
  
  @livewireStyles
</head>

<body class="font-sans antialiased selection:bg-primary-200 selection:text-primary min-h-screen flex flex-col">
  
  <!-- HEADER -->
  <header id="main-header" class="sticky top-0 z-50 border-b border-border/60 backdrop-blur-md bg-card/80 shadow-sm">
    <div class="page-container px-4 sm:px-6">
      <div class="flex items-center justify-between h-16 sm:h-20">
        
        <!-- Logo -->
        <a href="{{ route('home') }}" class="flex items-center gap-3 hover:opacity-80 transition-opacity" aria-label="Gramlyze Home">
          <div class="flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br from-primary to-secondary shadow-soft">
            <span class="text-xl sm:text-2xl font-bold text-white">G</span>
          </div>
          <span class="text-lg sm:text-xl font-bold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent hidden sm:inline">
            Gramlyze
          </span>
        </a>
        
        <!-- Desktop Navigation -->
        <nav class="hidden lg:flex items-center gap-6 text-sm font-medium">
          <a href="{{ route('catalog.tests-cards') }}" class="text-foreground/70 hover:text-foreground transition-colors">
            {{ __('public.nav.catalog') }}
          </a>
          <a href="{{ route('theory.index') }}" class="text-foreground/70 hover:text-foreground transition-colors">
            {{ __('public.nav.theory') }}
          </a>
          <a href="{{ route('words.test') }}" class="text-foreground/70 hover:text-foreground transition-colors">
            {{ __('public.nav.words') }}
          </a>
          <a href="{{ route('verbs.test') }}" class="text-foreground/70 hover:text-foreground transition-colors">
            {{ __('public.nav.verbs') }}
          </a>
        </nav>
        
        <!-- Actions -->
        <div class="flex items-center gap-2 sm:gap-3">
          
          <!-- Search Button (opens modal) -->
          <button 
            @click="$dispatch('open-search-modal')"
            class="flex items-center gap-2 px-3 py-2 rounded-xl border border-border bg-muted/30 hover:bg-muted transition-colors text-sm text-muted-foreground"
            aria-label="{{ __('public.search.button') }}"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <span class="hidden sm:inline">{{ __('public.search.placeholder') }}</span>
          </button>
          
          <!-- Language Switcher -->
          @include('components.language-switcher-v2')
          
          <!-- Theme Toggle -->
          <button 
            id="theme-toggle" 
            type="button" 
            class="p-2 rounded-xl border border-border hover:bg-muted transition-colors"
            aria-label="{{ __('public.footer.theme') }}"
          >
            <svg class="w-5 h-5 dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
            <svg class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
          </button>
          
          <!-- Mobile Menu Toggle -->
          <button 
            @click="mobileMenuOpen = !mobileMenuOpen"
            x-data="{ mobileMenuOpen: false }"
            class="lg:hidden p-2 rounded-xl border border-border hover:bg-muted transition-colors"
            aria-label="{{ __('public.nav.menu') }}"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
        </div>
      </div>
      
      <!-- Mobile Menu -->
      <div 
        x-data="{ mobileMenuOpen: false }"
        x-show="mobileMenuOpen"
        @click.away="mobileMenuOpen = false"
        @open-mobile-menu.window="mobileMenuOpen = true"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-4"
        class="lg:hidden border-t border-border py-4"
        style="display: none;"
      >
        <nav class="flex flex-col gap-3 text-sm font-medium">
          <a href="{{ route('catalog.tests-cards') }}" class="px-3 py-2 rounded-lg hover:bg-muted transition-colors">
            {{ __('public.nav.catalog') }}
          </a>
          <a href="{{ route('theory.index') }}" class="px-3 py-2 rounded-lg hover:bg-muted transition-colors">
            {{ __('public.nav.theory') }}
          </a>
          <a href="{{ route('words.test') }}" class="px-3 py-2 rounded-lg hover:bg-muted transition-colors">
            {{ __('public.nav.words') }}
          </a>
          <a href="{{ route('verbs.test') }}" class="px-3 py-2 rounded-lg hover:bg-muted transition-colors">
            {{ __('public.nav.verbs') }}
          </a>
        </nav>
      </div>
    </div>
  </header>
  
  <!-- Search Modal -->
  @include('components.search-modal-v2')
  
  <!-- MAIN CONTENT -->
  <main class="flex-1 page-container px-4 sm:px-6 py-8 sm:py-12">
    @yield('content')
  </main>
  
  <!-- FOOTER -->
  <footer class="border-t border-border bg-card mt-12">
    <div class="page-container px-4 sm:px-6 py-8 sm:py-12">
      <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
        
        <!-- Brand -->
        <div class="space-y-4">
          <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-primary to-secondary shadow-soft">
              <span class="text-xl font-bold text-white">G</span>
            </div>
            <span class="text-lg font-bold">Gramlyze</span>
          </div>
          <p class="text-sm text-muted-foreground">
            {{ __('public.footer.description') }}
          </p>
          <p class="text-xs text-muted-foreground">
            © <span id="footer-year"></span> Gramlyze. {{ __('public.footer.all_rights') }}
          </p>
        </div>
        
        <!-- Navigation -->
        <div class="space-y-3">
          <h3 class="text-sm font-semibold">{{ __('public.footer.navigation') }}</h3>
          <nav class="flex flex-col gap-2 text-sm text-muted-foreground">
            <a href="{{ route('catalog.tests-cards') }}" class="hover:text-foreground transition-colors">
              {{ __('public.nav.catalog') }}
            </a>
            <a href="{{ route('theory.index') }}" class="hover:text-foreground transition-colors">
              {{ __('public.nav.theory') }}
            </a>
            <a href="{{ route('words.test') }}" class="hover:text-foreground transition-colors">
              {{ __('public.nav.words') }}
            </a>
            <a href="{{ route('verbs.test') }}" class="hover:text-foreground transition-colors">
              {{ __('public.nav.verbs') }}
            </a>
          </nav>
        </div>
        
        <!-- Resources -->
        <div class="space-y-3">
          <h3 class="text-sm font-semibold">{{ __('public.footer.resources') }}</h3>
          <nav class="flex flex-col gap-2 text-sm text-muted-foreground">
            <a href="#" class="hover:text-foreground transition-colors">
              {{ __('public.footer.support') }}
            </a>
            <a href="#" class="hover:text-foreground transition-colors">
              {{ __('public.footer.terms') }}
            </a>
            <a href="#" class="hover:text-foreground transition-colors">
              {{ __('public.footer.policy') }}
            </a>
          </nav>
        </div>
        
        <!-- Trust Badges -->
        <div class="space-y-3">
          <h3 class="text-sm font-semibold">{{ __('public.footer.why_us') }}</h3>
          <div class="flex flex-col gap-2">
            <div class="flex items-center gap-2 text-sm text-muted-foreground">
              <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-success/10">
                <svg class="w-4 h-4 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
              </div>
              <span>{{ __('public.footer.data_security') }}</span>
            </div>
            <div class="flex items-center gap-2 text-sm text-muted-foreground">
              <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-info/10">
                <svg class="w-4 h-4 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
              </div>
              <span>{{ __('public.footer.quick_start') }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </footer>
  
  <!-- Scripts -->
  <script>
    // Set footer year
    document.getElementById('footer-year').textContent = new Date().getFullYear();
    
    // Dark mode toggle with persistence
    (function themeInit(){
      const saved = localStorage.getItem('theme');
      const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
      if (saved === 'dark' || (!saved && systemDark)) {
        document.documentElement.classList.add('dark');
      }
      
      document.getElementById('theme-toggle')?.addEventListener('click', () => {
        document.documentElement.classList.toggle('dark');
        const isDark = document.documentElement.classList.contains('dark');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
      });
    })();
    
    // Mobile menu toggle
    document.addEventListener('alpine:init', () => {
      Alpine.data('mobileMenu', () => ({
        open: false
      }));
    });
  </script>
  
  @livewireScripts
  @yield('scripts')
</body>
</html>
