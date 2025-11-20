<!doctype html>
<html lang="uk" class="scroll-smooth">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>@yield('title', 'Gramlyze — English Learning Platform')</title>
  <meta name="description" content="@yield('description', 'Gramlyze is a comprehensive platform for English teachers to create tests, analyze answers, and collaborate effectively.')" />

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="//unpkg.com/alpinejs" defer></script>
  
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          fontFamily: { 
            sans: ['Inter', 'system-ui', 'sans-serif'],
            display: ['Space Grotesk', 'system-ui', 'sans-serif']
          },
          colors: {
            primary: { 
              50: '#f0fdfa',
              100: '#ccfbf1', 
              200: '#99f6e4',
              300: '#5eead4',
              400: '#2dd4bf',
              500: '#14b8a6',
              600: '#0d9488',
              700: '#0f766e',
              800: '#115e59',
              900: '#134e4a',
            },
          },
          animation: {
            'fade-in': 'fadeIn 0.5s ease-in-out',
            'slide-up': 'slideUp 0.6s ease-out',
          },
          keyframes: {
            fadeIn: {
              '0%': { opacity: '0' },
              '100%': { opacity: '1' },
            },
            slideUp: {
              '0%': { transform: 'translateY(20px)', opacity: '0' },
              '100%': { transform: 'translateY(0)', opacity: '1' },
            }
          }
        }
      }
    }
  </script>

  <style>
    :root {
      color-scheme: light dark;
    }
    
    .dark {
      color-scheme: dark;
    }

    body {
      font-family: 'Inter', system-ui, sans-serif;
    }

    .font-display {
      font-family: 'Space Grotesk', system-ui, sans-serif;
    }

    /* Custom scrollbar */
    ::-webkit-scrollbar {
      width: 10px;
      height: 10px;
    }

    ::-webkit-scrollbar-track {
      background: transparent;
    }

    ::-webkit-scrollbar-thumb {
      background: #cbd5e1;
      border-radius: 5px;
    }

    .dark ::-webkit-scrollbar-thumb {
      background: #475569;
    }

    ::-webkit-scrollbar-thumb:hover {
      background: #94a3b8;
    }

    /* Smooth animations */
    [data-animate] {
      opacity: 0;
      transform: translateY(30px);
      transition: opacity 0.7s ease, transform 0.7s ease;
    }

    [data-animate].visible {
      opacity: 1;
      transform: translateY(0);
    }

    /* Glass effect */
    .glass {
      background: rgba(255, 255, 255, 0.7);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
    }

    .dark .glass {
      background: rgba(15, 23, 42, 0.7);
    }
  </style>
</head>

<body class="bg-gradient-to-br from-slate-50 via-white to-teal-50 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950 text-slate-900 dark:text-slate-100 antialiased">
  
  <!-- Header -->
  <header class="sticky top-0 z-50 glass border-b border-slate-200/50 dark:border-slate-700/50">
    <nav class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="flex h-16 items-center justify-between">
        <!-- Logo -->
        <div class="flex items-center">
          <a href="{{ route('home') }}" class="flex items-center space-x-2" aria-label="Gramlyze Home">
            <x-gramlyze-logo-new />
          </a>
        </div>

        <!-- Desktop Navigation -->
        <div class="hidden md:flex md:items-center md:space-x-1">
          <a href="{{ route('catalog-tests.cards') }}" class="px-4 py-2 text-sm font-medium text-slate-700 hover:text-teal-600 dark:text-slate-300 dark:hover:text-teal-400 transition-colors rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
            Catalog
          </a>
          <a href="{{ route('pages.index') }}" class="px-4 py-2 text-sm font-medium text-slate-700 hover:text-teal-600 dark:text-slate-300 dark:hover:text-teal-400 transition-colors rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
            Theory
          </a>
          <a href="{{ route('question-review.index') }}" class="px-4 py-2 text-sm font-medium text-slate-700 hover:text-teal-600 dark:text-slate-300 dark:hover:text-teal-400 transition-colors rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
            Reviews
          </a>
          <a href="{{ route('site.search') }}" class="px-4 py-2 text-sm font-medium text-slate-700 hover:text-teal-600 dark:text-slate-300 dark:hover:text-teal-400 transition-colors rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </a>
        </div>

        <!-- CTA Button -->
        <div class="hidden md:flex md:items-center md:space-x-3">
          <button id="theme-toggle-desktop" type="button" class="p-2 text-slate-600 hover:text-teal-600 dark:text-slate-400 dark:hover:text-teal-400 transition-colors rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="Toggle theme">
            <svg class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <svg class="w-5 h-5 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
          </button>
          <a href="{{ route('grammar-test') }}" class="inline-flex items-center px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-teal-600 to-cyan-600 rounded-lg hover:from-teal-700 hover:to-cyan-700 transition-all shadow-sm hover:shadow-md">
            Create Test
            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </a>
        </div>

        <!-- Mobile menu button -->
        <div class="flex items-center md:hidden">
          <button id="mobile-menu-button" type="button" class="p-2 text-slate-600 hover:text-teal-600 dark:text-slate-400 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="Open menu">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Mobile menu -->
      <div id="mobile-menu" class="hidden md:hidden pb-4 pt-2 space-y-1">
        <a href="{{ route('catalog-tests.cards') }}" class="block px-4 py-2 text-base font-medium text-slate-700 hover:text-teal-600 dark:text-slate-300 dark:hover:text-teal-400 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
          Catalog
        </a>
        <a href="{{ route('pages.index') }}" class="block px-4 py-2 text-base font-medium text-slate-700 hover:text-teal-600 dark:text-slate-300 dark:hover:text-teal-400 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
          Theory
        </a>
        <a href="{{ route('question-review.index') }}" class="block px-4 py-2 text-base font-medium text-slate-700 hover:text-teal-600 dark:text-slate-300 dark:hover:text-teal-400 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
          Reviews
        </a>
        <a href="{{ route('site.search') }}" class="block px-4 py-2 text-base font-medium text-slate-700 hover:text-teal-600 dark:text-slate-300 dark:hover:text-teal-400 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
          Search
        </a>
        <div class="pt-2 border-t border-slate-200 dark:border-slate-700">
          <a href="{{ route('grammar-test') }}" class="block px-4 py-2.5 text-center font-semibold text-white bg-gradient-to-r from-teal-600 to-cyan-600 rounded-lg">
            Create Test
          </a>
        </div>
      </div>
    </nav>
  </header>

  <!-- Main Content -->
  <main class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8 md:py-12">
    @yield('content')
  </main>

  <!-- Footer -->
  <footer class="mt-20 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
        <!-- Brand -->
        <div class="md:col-span-2">
          <x-gramlyze-logo-new class="mb-4" />
          <p class="text-sm text-slate-600 dark:text-slate-400 mb-4 max-w-md">
            A comprehensive platform for English teachers and educators. Create tests, analyze student performance, and collaborate with your team.
          </p>
          <div class="flex space-x-4">
            <a href="#" class="text-slate-400 hover:text-teal-600 dark:hover:text-teal-400 transition-colors">
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/>
              </svg>
            </a>
            <a href="#" class="text-slate-400 hover:text-teal-600 dark:hover:text-teal-400 transition-colors">
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
              </svg>
            </a>
          </div>
        </div>

        <!-- Quick Links -->
        <div>
          <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100 uppercase tracking-wider mb-4">Platform</h3>
          <ul class="space-y-3">
            <li><a href="{{ route('catalog-tests.cards') }}" class="text-sm text-slate-600 hover:text-teal-600 dark:text-slate-400 dark:hover:text-teal-400 transition-colors">Test Catalog</a></li>
            <li><a href="{{ route('pages.index') }}" class="text-sm text-slate-600 hover:text-teal-600 dark:text-slate-400 dark:hover:text-teal-400 transition-colors">Theory Pages</a></li>
            <li><a href="{{ route('question-review.index') }}" class="text-sm text-slate-600 hover:text-teal-600 dark:text-slate-400 dark:hover:text-teal-400 transition-colors">Question Reviews</a></li>
            <li><a href="{{ route('grammar-test') }}" class="text-sm text-slate-600 hover:text-teal-600 dark:text-slate-400 dark:hover:text-teal-400 transition-colors">Test Builder</a></li>
          </ul>
        </div>

        <!-- Support -->
        <div>
          <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100 uppercase tracking-wider mb-4">Support</h3>
          <ul class="space-y-3">
            <li><a href="{{ route('login.show') }}" class="text-sm text-slate-600 hover:text-teal-600 dark:text-slate-400 dark:hover:text-teal-400 transition-colors">Admin Login</a></li>
            <li><a href="#" class="text-sm text-slate-600 hover:text-teal-600 dark:text-slate-400 dark:hover:text-teal-400 transition-colors">Documentation</a></li>
            <li><a href="#" class="text-sm text-slate-600 hover:text-teal-600 dark:text-slate-400 dark:hover:text-teal-400 transition-colors">Contact Us</a></li>
            <li>
              <button id="theme-toggle-footer" type="button" class="text-sm text-slate-600 hover:text-teal-600 dark:text-slate-400 dark:hover:text-teal-400 transition-colors">
                Toggle Theme
              </button>
            </li>
          </ul>
        </div>
      </div>

      <div class="mt-8 pt-8 border-t border-slate-200 dark:border-slate-800">
        <p class="text-sm text-slate-500 dark:text-slate-400 text-center">
          &copy; <span id="year"></span> Gramlyze. All rights reserved.
        </p>
      </div>
    </div>
  </footer>

  <script>
    // Theme toggle
    (function() {
      const theme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
      if (theme === 'dark') {
        document.documentElement.classList.add('dark');
      }

      function toggleTheme() {
        document.documentElement.classList.toggle('dark');
        localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
      }

      document.getElementById('theme-toggle-desktop')?.addEventListener('click', toggleTheme);
      document.getElementById('theme-toggle-footer')?.addEventListener('click', toggleTheme);
    })();

    // Mobile menu
    document.getElementById('mobile-menu-button')?.addEventListener('click', function() {
      document.getElementById('mobile-menu')?.classList.toggle('hidden');
    });

    // Current year
    document.getElementById('year').textContent = new Date().getFullYear();

    // Scroll animations
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
        }
      });
    }, { threshold: 0.1 });

    document.querySelectorAll('[data-animate]').forEach(el => observer.observe(el));
  </script>

  @yield('scripts')
</body>
</html>
