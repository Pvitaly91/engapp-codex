<!doctype html>
<html lang="uk" class="h-full">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>@yield('title', 'Gramlyze — Платформа для викладачів англійської')</title>
  <meta name="description" content="Gramlyze допомагає збирати тести, аналізувати відповіді та координувати команду викладачів англійської." />

  <!-- Google Font: Montserrat -->
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">

  <!-- Tailwind CDN + runtime config -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="//unpkg.com/alpinejs" defer></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          fontFamily: { sans: ['Montserrat', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
          colors: {
            border: 'hsl(var(--border))',
            input: 'hsl(var(--input))',
            ring: 'hsl(var(--ring))',
            background: 'hsl(var(--background))',
            foreground: 'hsl(var(--foreground))',
            primary: { DEFAULT: 'hsl(var(--primary))', foreground: 'hsl(var(--primary-foreground))' },
            secondary: { DEFAULT: 'hsl(var(--secondary))', foreground: 'hsl(var(--secondary-foreground))' },
            muted: { DEFAULT: 'hsl(var(--muted))', foreground: 'hsl(var(--muted-foreground))' },
            accent: { DEFAULT: 'hsl(var(--accent))', foreground: 'hsl(var(--accent-foreground))' },
            popover: { DEFAULT: 'hsl(var(--popover))', foreground: 'hsl(var(--popover-foreground))' },
            card: { DEFAULT: 'hsl(var(--card))', foreground: 'hsl(var(--card-foreground))' },
            success: 'hsl(var(--success))',
            warning: 'hsl(var(--warning))',
            destructive: { DEFAULT: 'hsl(var(--destructive))', foreground: 'hsl(var(--destructive-foreground))' },
            info: 'hsl(var(--info))',
          },
          borderRadius: { xl: '0.75rem', '2xl': '1rem', '3xl': '1.5rem' },
          boxShadow: { soft: '0 10px 30px -12px rgba(0,0,0,0.15)' }
        }
      }
    }
  </script>

  <!-- Design tokens (light/dark) -->
  <style>
    :root {
      /* Vibrant Surfaces */
      --background: 210 40% 98%;
      --foreground: 215 31% 18%;
      --card: 0 0% 100%;
      --card-foreground: 215 31% 18%;
      --popover: 0 0% 100%;
      --popover-foreground: 215 31% 18%;

      /* Vivid Brand / Semantic */
      --primary: 253 85% 63%;
      --primary-foreground: 0 0% 100%;

      --secondary: 188 82% 47%;
      --secondary-foreground: 0 0% 100%;

      --muted: 210 40% 96%;
      --muted-foreground: 215 16% 40%;

      --accent: 31 94% 55%;
      --accent-foreground: 0 0% 100%;

      --destructive: 0 84% 60%;
      --destructive-foreground: 0 0% 100%;

      --success: 142 76% 36%;
      --warning: 38 92% 50%;
      --info: 217 91% 60%;

      --border: 214 32% 89%;
      --input: 214 32% 89%;
      --ring: 253 85% 63%;
    }
    .dark {
      --background: 222 15% 10%;
      --foreground: 0 0% 98%;
      --card: 222 15% 13%;
      --card-foreground: 0 0% 98%;
      --popover: 222 15% 13%;
      --popover-foreground: 0 0% 98%;

      --primary: 253 85% 70%;
      --primary-foreground: 0 0% 10%;

      --secondary: 188 85% 52%;
      --secondary-foreground: 0 0% 10%;

      --muted: 222 15% 16%;
      --muted-foreground: 0 0% 80%;

      --accent: 31 94% 60%;
      --accent-foreground: 0 0% 10%;

      --destructive: 0 72% 55%;
      --destructive-foreground: 0 0% 100%;

      --success: 142 55% 45%;
      --warning: 38 90% 55%;
      --info: 217 80% 65%;

      --border: 222 15% 22%;
      --input: 222 15% 22%;
      --ring: 253 85% 63%;
    }
    html, body { height: 100%; }
    body { background: radial-gradient(circle at 15% 20%, hsla(var(--primary), 0.09), transparent 25%), radial-gradient(circle at 90% 10%, hsla(var(--secondary), 0.07), transparent 20%), hsl(var(--background)); color: hsl(var(--foreground)); }
    .page-shell { max-width: 80rem; }
    [data-animate] {
      opacity: 0;
      transform: translateY(32px);
      transition: opacity 0.8s ease, transform 0.8s ease;
      transition-delay: var(--animate-delay, 0s);
    }
    [data-animate].is-visible {
      opacity: 1;
      transform: translateY(0);
    }
    [data-slider-track] {
      scroll-snap-type: x mandatory;
      -webkit-overflow-scrolling: touch;
    }
    [data-slider-track]::-webkit-scrollbar {
      display: none;
    }
    [data-slider-track] {
      -ms-overflow-style: none;
      scrollbar-width: none;
    }
    .slider-dot {
      width: 0.5rem;
      height: 0.5rem;
      border-radius: 9999px;
      background-color: hsla(var(--border), 0.7);
      transition: background-color 0.3s ease, transform 0.3s ease;
    }
    .slider-dot.is-active {
      background-color: hsl(var(--primary));
      transform: scale(1.1);
    }
  </style>

  @livewireStyles
</head>

<body class="font-sans antialiased selection:bg-primary/15 selection:text-primary">
  <div class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_25%_20%,hsla(var(--primary),0.08),transparent_25%),radial-gradient(circle_at_80%_0%,hsla(var(--accent),0.08),transparent_20%),linear-gradient(135deg,hsla(var(--secondary),0.05),transparent_40%)]"></div>
  <!-- HEADER / NAV -->
  <header id="main-header" class="sticky top-0 z-40 border-b border-border/70 backdrop-blur bg-background/85 transition-transform duration-300">
    <div class="page-shell mx-auto px-3 sm:px-4">
      <div class="flex flex-wrap items-center justify-between gap-4 py-4 lg:h-20 lg:flex-nowrap">
        <a href="{{ route('home') }}" class="flex items-center gap-3 flex-shrink-0" aria-label="Gramlyze">
          <x-gramlyze-logo class="hidden md:inline-flex" />
          <x-gramlyze-logo variant="compact" class="md:hidden" />
        </a>
        <form action="{{ route('site.search') }}" method="GET" class="relative hidden md:block">
          <input type="search" name="q" id="search-box" autocomplete="off" placeholder="Пошук матеріалів" class="w-56 rounded-xl border border-input bg-background px-4 py-2 text-sm shadow-sm focus:border-primary focus:outline-none" />
          <div id="search-box-list" class="absolute left-0 mt-1 w-full bg-background border border-border rounded-xl shadow-soft text-sm hidden z-50"></div>
        </form>
        <div class="flex items-center gap-2 lg:hidden">
          <button id="mobile-search-btn" class="rounded-xl border border-border p-2 text-sm md:hidden" aria-expanded="false" aria-controls="mobile-search">🔍<span class="sr-only">Пошук</span></button>
          <button id="mobile-menu-toggle" class="rounded-xl border border-border p-2 text-sm" aria-expanded="false" aria-controls="primary-nav">
            <span class="sr-only">Меню</span>
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="4" y1="6" x2="20" y2="6" />
              <line x1="4" y1="12" x2="20" y2="12" />
              <line x1="4" y1="18" x2="20" y2="18" />
            </svg>
          </button>
        </div>
        <nav id="primary-nav" class="order-3 hidden w-full flex flex-col gap-3 border-t border-border/70 pt-3 text-sm font-medium lg:order-none lg:flex lg:w-auto lg:flex-row lg:items-center lg:gap-6 lg:border-0 lg:pt-0">
          <a class="text-muted-foreground transition hover:text-foreground" href="{{ route('catalog.tests-cards') }}">Каталог</a>
          <a class="text-muted-foreground transition hover:text-foreground" href="{{ route('theory.index') }}">Теорія</a>
          <a class="text-muted-foreground transition hover:text-foreground" href="{{ route('question-review.index') }}">Рецензії</a>
          <a class="text-muted-foreground transition hover:text-foreground" href="#ai-toolkit">AI Toolkit</a>
          <a class="text-muted-foreground transition hover:text-foreground" href="#team-collaboration">Командам</a>
          <a class="inline-flex items-center gap-2 rounded-full bg-primary px-4 py-2 text-white shadow-md transition hover:-translate-y-0.5 hover:shadow-lg" href="{{ route('grammar-test') }}">Розпочати</a>
        </nav>
      </div>
      <div id="mobile-search" class="md:hidden hidden pb-3">
        <form action="{{ route('site.search') }}" method="GET" class="relative">
          <input type="search" name="q" id="search-box-mobile" autocomplete="off" placeholder="Пошук матеріалів" class="mt-3 w-full rounded-xl border border-input bg-background px-3 py-2 text-sm shadow-sm" />
          <div id="search-box-mobile-list" class="absolute left-0 right-0 mt-1 bg-background border border-border rounded-xl shadow-soft text-sm hidden z-50"></div>
        </form>
      </div>
    </div>
  </header>

  <main class="page-shell mx-auto px-3 sm:px-4 py-8 sm:py-10">
    @yield('content')
    {{ $slot ?? '' }}
  </main>

  <footer class="border-t border-border mt-12 py-8 text-sm">
    <div class="page-shell mx-auto px-4 grid gap-6 md:grid-cols-[1.2fr_1fr] md:items-center">
      <div class="space-y-3">
        <div class="flex items-center gap-2">
          <x-gramlyze-logo variant="compact" size="h-9 w-9" />
          <span class="font-semibold">Gramlyze <span id="year"></span></span>
        </div>
        <p class="text-muted-foreground max-w-2xl">Платформа для викладачів та методистів англійської мови: тести, AI-аналіз, база знань і спільна робота команди.</p>
        <div class="flex flex-wrap gap-3 text-xs text-muted-foreground">
          <span class="inline-flex items-center gap-1 rounded-full bg-muted px-3 py-1">🔒 Безпека даних</span>
          <span class="inline-flex items-center gap-1 rounded-full bg-muted px-3 py-1">🤝 Підтримка команди</span>
          <span class="inline-flex items-center gap-1 rounded-full bg-muted px-3 py-1">⚡ Швидкий старт</span>
        </div>
      </div>
      <div class="flex flex-col gap-3 md:items-end">
        <div class="flex flex-wrap gap-4 text-sm md:justify-end">
          <a class="text-muted-foreground hover:text-foreground" href="#">Політика</a>
          <a class="text-muted-foreground hover:text-foreground" href="#">Умови</a>
          <a class="text-muted-foreground hover:text-foreground" href="#faq">Підтримка</a>
        </div>
        <div class="flex items-center gap-3">
          <button id="theme-toggle" type="button" class="rounded-full border border-border px-3 py-1.5 text-xs font-semibold text-muted-foreground transition hover:text-foreground">Тема</button>
          <a href="{{ route('login.show') }}" class="rounded-full border border-border px-4 py-2 text-sm font-semibold text-foreground transition hover:border-primary hover:text-primary">Вхід до адмінки</a>
        </div>
      </div>
    </div>
  </footer>

  <script>
    // Dark mode toggle with persistence
    (function themeInit(){
      const saved = localStorage.getItem('theme');
      const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
      if (saved === 'dark' || (!saved && systemDark)) document.documentElement.classList.add('dark');
      document.getElementById('theme-toggle')?.addEventListener('click', () => {
        document.documentElement.classList.toggle('dark');
        const isDark = document.documentElement.classList.contains('dark');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
      });
    })();

    document.getElementById('year').textContent = new Date().getFullYear();
    const mobileSearchBtn = document.getElementById('mobile-search-btn');
    const mobileSearchPanel = document.getElementById('mobile-search');
    mobileSearchBtn?.addEventListener('click', () => {
      const expanded = mobileSearchBtn.getAttribute('aria-expanded') === 'true';
      mobileSearchBtn.setAttribute('aria-expanded', (!expanded).toString());
      mobileSearchPanel?.classList.toggle('hidden', expanded);
    });

    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    const primaryNav = document.getElementById('primary-nav');
    mobileMenuToggle?.addEventListener('click', () => {
      const expanded = mobileMenuToggle.getAttribute('aria-expanded') === 'true';
      mobileMenuToggle.setAttribute('aria-expanded', (!expanded).toString());
      primaryNav?.classList.toggle('hidden', expanded);
    });
    primaryNav?.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', () => {
        if (window.matchMedia('(max-width: 1023px)').matches) {
          mobileMenuToggle?.setAttribute('aria-expanded', 'false');
          primaryNav.classList.add('hidden');
        }
      });
    });

    function setupPredictiveSearch(inputId, listId) {
      const input = document.getElementById(inputId);
      const list = document.getElementById(listId);
      if (!input || !list) return;
      let controller;
      input.addEventListener('input', async () => {
        const q = input.value.trim();
        if (q.length < 2) { list.innerHTML = ''; list.classList.add('hidden'); return; }
        controller?.abort();
        controller = new AbortController();
        try {
          const res = await fetch(input.form.action + '?q=' + encodeURIComponent(q), {
            headers: { 'Accept': 'application/json' },
            signal: controller.signal,
          });
          const data = await res.json();
          if (!data.length) { list.innerHTML = ''; list.classList.add('hidden'); return; }
          list.innerHTML = data.map(item => `<a href="${item.url}" class="block px-3 py-2 hover:bg-muted">${item.title}</a>`).join('');
          list.classList.remove('hidden');
        } catch (e) {}
      });
      input.addEventListener('blur', () => setTimeout(() => list.classList.add('hidden'), 200));
    }
    setupPredictiveSearch('search-box', 'search-box-list');
    setupPredictiveSearch('search-box-mobile', 'search-box-mobile-list');

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (!prefersReducedMotion && 'IntersectionObserver' in window) {
      const animateObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-visible');
            animateObserver.unobserve(entry.target);
          }
        });
      }, { threshold: 0.2 });

      document.querySelectorAll('[data-animate]').forEach((el) => {
        const delay = el.getAttribute('data-animate-delay');
        if (delay) {
          const milliseconds = parseInt(delay, 10);
          if (!Number.isNaN(milliseconds)) {
            el.style.setProperty('--animate-delay', `${milliseconds}ms`);
          }
        }
        if (el.classList.contains('is-visible')) return;
        animateObserver.observe(el);
      });
    } else {
      document.querySelectorAll('[data-animate]').forEach((el) => el.classList.add('is-visible'));
    }

    function initSlider(slider) {
      const track = slider.querySelector('[data-slider-track]');
      if (!track) return;
      const items = Array.from(track.children);
      if (!items.length) return;
      const prev = slider.querySelector('[data-slider-prev]');
      const next = slider.querySelector('[data-slider-next]');
      const dotsContainer = slider.querySelector('[data-slider-dots]');
      if (items.length <= 1) {
        prev?.classList.add('hidden');
        next?.classList.add('hidden');
        dotsContainer?.classList.add('hidden');
        return;
      }
      let activeIndex = 0;

      function scrollToIndex(index) {
        const target = items[index];
        if (!target) return;
        const offset = target.offsetLeft - items[0].offsetLeft;
        track.scrollTo({ left: offset, behavior: 'smooth' });
      }

      function updateControls() {
        if (prev) prev.disabled = activeIndex === 0;
        if (next) next.disabled = activeIndex === items.length - 1;
        if (dotsContainer) {
          dotsContainer.querySelectorAll('button').forEach((dot, idx) => {
            dot.classList.toggle('is-active', idx === activeIndex);
          });
        }
      }

      let ticking = false;
      track.addEventListener('scroll', () => {
        if (!ticking) {
          window.requestAnimationFrame(() => {
            const scrollLeft = track.scrollLeft + track.clientWidth / 2;
            let newIndex = items.findIndex((item) => scrollLeft < item.offsetLeft - items[0].offsetLeft + item.clientWidth);
            if (newIndex === -1) newIndex = items.length - 1;
            if (newIndex !== activeIndex) {
              activeIndex = newIndex;
              updateControls();
            }
            ticking = false;
          });
          ticking = true;
        }
      }, { passive: true });

      if (prev) {
        prev.addEventListener('click', () => {
          activeIndex = Math.max(0, activeIndex - 1);
          scrollToIndex(activeIndex);
          updateControls();
        });
      }

      if (next) {
        next.addEventListener('click', () => {
          activeIndex = Math.min(items.length - 1, activeIndex + 1);
          scrollToIndex(activeIndex);
          updateControls();
        });
      }

      if (dotsContainer) {
        dotsContainer.innerHTML = '';
        items.forEach((_item, idx) => {
          const dot = document.createElement('button');
          dot.type = 'button';
          dot.className = 'slider-dot';
          dot.setAttribute('aria-label', `Перейти до слайда ${idx + 1}`);
          dot.addEventListener('click', () => {
            activeIndex = idx;
            scrollToIndex(activeIndex);
            updateControls();
          });
          dotsContainer.appendChild(dot);
        });
      }

      updateControls();
    }

    document.querySelectorAll('[data-slider]').forEach(initSlider);

    // Hide header on scroll down, show on scroll up
    (function headerScrollBehavior() {
      const header = document.getElementById('main-header');
      if (!header) return;
      
      let lastScrollTop = 0;
      const scrollThreshold = 50; // Minimum scroll before hiding
      
      // Get current header height and update CSS variable
      function getAndUpdateHeaderHeight() {
        const height = header.offsetHeight;
        document.documentElement.style.setProperty('--header-height', height + 'px');
        return height;
      }
      
      // Initial header height
      let headerHeight = getAndUpdateHeaderHeight();
      
      function updateHeaderVisibility() {
        const scrollTop = window.scrollY || document.documentElement.scrollTop;
        
        // Don't hide if we're near the top
        if (scrollTop < scrollThreshold) {
          header.style.transform = 'translateY(0)';
          document.documentElement.style.setProperty('--header-visible', '1');
          lastScrollTop = scrollTop;
          return;
        }
        
        // Scrolling down - hide header
        if (scrollTop > lastScrollTop && scrollTop > headerHeight) {
          header.style.transform = 'translateY(-100%)';
          document.documentElement.style.setProperty('--header-visible', '0');
        } 
        // Scrolling up - show header
        else if (scrollTop < lastScrollTop) {
          header.style.transform = 'translateY(0)';
          document.documentElement.style.setProperty('--header-visible', '1');
        }
        
        lastScrollTop = scrollTop;
      }
      
      // Set initial CSS variable
      document.documentElement.style.setProperty('--header-visible', '1');
      
      // Update header height on resize (debounced)
      let resizeTimeout;
      window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function() {
          headerHeight = getAndUpdateHeaderHeight();
        }, 100);
      }, { passive: true });
      
      // Throttled scroll listener
      let ticking = false;
      window.addEventListener('scroll', function() {
        if (!ticking) {
          window.requestAnimationFrame(function() {
            updateHeaderVisibility();
            ticking = false;
          });
          ticking = true;
        }
      }, { passive: true });
    })();

    // Sidebar positioning helper - adjusts sidebar position when header hides/shows
    // Works with any element that has id="theory-sidebar"
    (function initSidebarPositioning() {
      const sidebar = document.getElementById('theory-sidebar');
      const header = document.getElementById('main-header');
      
      if (!sidebar || !header) return;
      
      // Use CSS custom property set by the header script, with fallback
      function getHeaderHeight() {
        const cssHeight = getComputedStyle(document.documentElement).getPropertyValue('--header-height');
        if (cssHeight) {
          return parseInt(cssHeight, 10) || header.offsetHeight;
        }
        return header.offsetHeight;
      }
      
      const topWhenHeaderHidden = 16; // Small padding from top when header is hidden
      
      function updateSidebarPosition() {
        // Use only the CSS custom property for consistent state tracking
        const headerVisible = getComputedStyle(document.documentElement).getPropertyValue('--header-visible').trim() === '1';
        const headerHeight = getHeaderHeight();
        const topWhenHeaderVisible = headerHeight + 16; // 16px = 1rem padding
        
        if (headerVisible) {
          sidebar.style.top = topWhenHeaderVisible + 'px';
        } else {
          sidebar.style.top = topWhenHeaderHidden + 'px';
        }
      }
      
      // Initial position - wait a tick for CSS variables to be set
      requestAnimationFrame(updateSidebarPosition);
      
      // Throttled scroll listener
      let sidebarTicking = false;
      window.addEventListener('scroll', function() {
        if (!sidebarTicking) {
          window.requestAnimationFrame(function() {
            updateSidebarPosition();
            sidebarTicking = false;
          });
          sidebarTicking = true;
        }
      }, { passive: true });
      
      // Also listen for transitionend on header to catch animation completion
      header.addEventListener('transitionend', updateSidebarPosition);
    })();
  </script>

  <style>
    /* Custom scrollbar for scrollable navigation elements */
    #category-nav-scroll::-webkit-scrollbar,
    #theory-sidebar::-webkit-scrollbar {
      width: 4px;
    }
    #category-nav-scroll::-webkit-scrollbar-track,
    #theory-sidebar::-webkit-scrollbar-track {
      background: transparent;
    }
    #category-nav-scroll::-webkit-scrollbar-thumb,
    #theory-sidebar::-webkit-scrollbar-thumb {
      background: hsl(var(--border));
      border-radius: 2px;
    }
    #category-nav-scroll::-webkit-scrollbar-thumb:hover,
    #theory-sidebar::-webkit-scrollbar-thumb:hover {
      background: hsl(var(--muted-foreground));
    }
    #category-nav-scroll,
    #theory-sidebar {
      scrollbar-width: thin;
      scrollbar-color: hsl(var(--border)) transparent;
    }

    /* Sticky header compact state when scrolled - DESKTOP ONLY (>= 1024px) */
    .sticky-test-header {
      --sticky-transition-duration: 200ms;
    }

    .sticky-test-header .sticky-inner,
    .sticky-test-header .progress-section,
    .sticky-test-header .progress-icon,
    .sticky-test-header .progress-icon svg,
    .sticky-test-header .progress-label-text,
    .sticky-test-header .progress-value,
    .sticky-test-header .progress-bar-container,
    .sticky-test-header .word-search-section {
      transition: all var(--sticky-transition-duration) ease;
    }

    /* Desktop-only sticky compact state (min-width: 1024px) */
    @media (min-width: 1024px) {
      .sticky-test-header.is-stuck .sticky-inner {
        padding: 0.5rem 0.75rem;
        border-radius: 0.75rem;
        gap: 0.25rem;
      }

      .sticky-test-header.is-stuck .progress-section {
        padding: 0.25rem 0.5rem;
        border-radius: 0.5rem;
      }

      .sticky-test-header.is-stuck .progress-icon {
        width: 1.5rem;
        height: 1.5rem;
      }

      .sticky-test-header.is-stuck .progress-icon svg {
        width: 0.75rem;
        height: 0.75rem;
      }

      .sticky-test-header.is-stuck .progress-label-text {
        font-size: 0.5625rem;
        line-height: 1;
      }

      .sticky-test-header.is-stuck .progress-value {
        font-size: 0.8125rem;
        line-height: 1.2;
      }

      .sticky-test-header.is-stuck .progress-bar-container {
        height: 0.25rem;
        margin-top: 0.125rem;
      }

      /* Hide word search section when stuck to reduce height by ~50% */
      .sticky-test-header.is-stuck .word-search-section {
        display: none;
      }

      /* Show search button when stuck */
      .sticky-test-header.is-stuck .sticky-search-btn {
        display: flex;
      }

      /* When search is expanded in stuck mode, show the word search section */
      .sticky-test-header.is-stuck.search-expanded .word-search-section {
        display: block;
      }

      /* Hide search button when search is expanded */
      .sticky-test-header.is-stuck.search-expanded .sticky-search-btn {
        display: none;
      }

      /* Additional space adjustments for flex containers */
      .sticky-test-header.is-stuck .progress-section > div {
        gap: 0.375rem;
      }
    }
  </style>

  @livewireScripts
  @yield('scripts')
</body>
</html>
