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
      --background: 0 0% 100%;
      --foreground: 15 7% 11%;
      --card: 0 0% 100%;
      --card-foreground: 15 7% 11%;
      --popover: 0 0% 100%;
      --popover-foreground: 15 7% 11%;

      /* Vivid Brand / Semantic */
      --primary: 262 83% 58%;
      --primary-foreground: 0 0% 100%;

      --secondary: 188 85% 45%;
      --secondary-foreground: 0 0% 100%;

      --muted: 0 0% 96%;
      --muted-foreground: 15 7% 35%;

      --accent: 24 94% 50%;
      --accent-foreground: 0 0% 100%;

      --destructive: 0 84% 60%;
      --destructive-foreground: 0 0% 100%;

      --success: 142 76% 36%;
      --warning: 38 92% 50%;
      --info: 217 91% 60%;

      --border: 0 0% 88%;
      --input: 0 0% 88%;
      --ring: 262 83% 58%;
    }
    .dark {
      --background: 222 15% 10%;
      --foreground: 0 0% 98%;
      --card: 222 15% 13%;
      --card-foreground: 0 0% 98%;
      --popover: 222 15% 13%;
      --popover-foreground: 0 0% 98%;

      --primary: 262 91% 70%;
      --primary-foreground: 0 0% 10%;

      --secondary: 188 85% 52%;
      --secondary-foreground: 0 0% 10%;

      --muted: 222 15% 16%;
      --muted-foreground: 0 0% 80%;

      --accent: 24 94% 55%;
      --accent-foreground: 0 0% 10%;

      --destructive: 0 72% 55%;
      --destructive-foreground: 0 0% 100%;

      --success: 142 55% 45%;
      --warning: 38 90% 55%;
      --info: 217 80% 65%;

      --border: 222 15% 22%;
      --input: 222 15% 22%;
      --ring: 262 83% 60%;
    }
    html, body { height: 100%; }
    body { background: hsl(var(--background)); color: hsl(var(--foreground)); }
    .container { max-width: 72rem; }
    [data-animate] {
      opacity: 0;
      transform: translateY(var(--animate-distance, 30px));
      transition: opacity 0.7s ease, transform 0.7s ease;
      transition-delay: var(--animate-delay, 0s);
    }
    [data-animate].animate-in {
      opacity: 1;
      transform: translateY(0);
    }
    [data-animate][data-animate-type="fade-up"] {
      --animate-distance: 40px;
    }
    @media (prefers-reduced-motion: reduce) {
      [data-animate],
      [data-animate].animate-in {
        transition-duration: 0.01ms !important;
        transition-delay: 0ms !important;
        transform: none !important;
        opacity: 1 !important;
      }
    }
    [data-slider-track] {
      scroll-snap-type: x mandatory;
      -webkit-overflow-scrolling: touch;
    }
    [data-slider-track]::-webkit-scrollbar {
      display: none;
    }
    [data-slide] {
      scroll-snap-align: start;
      flex: 0 0 85%;
      max-width: 85%;
    }
    @media (min-width: 640px) {
      [data-slide] {
        flex-basis: 75%;
        max-width: 75%;
      }
    }
    @media (min-width: 768px) {
      [data-slider-track] {
        scroll-snap-type: none;
      }
      [data-slide] {
        flex: initial;
        max-width: none;
      }
    }
    .slider-nav-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 2.75rem;
      height: 2.75rem;
      border-radius: 9999px;
      border: 1px solid hsl(var(--border));
      background: hsl(var(--background));
      color: hsl(var(--foreground));
      box-shadow: 0 10px 30px -12px rgba(0, 0, 0, 0.15);
      transition: border-color 0.2s ease, transform 0.2s ease, color 0.2s ease;
    }
    .slider-nav-btn:hover {
      border-color: hsl(var(--primary));
      color: hsl(var(--primary));
      transform: translateY(-2px);
    }
    .slider-nav-btn[disabled] {
      opacity: 0.4;
      cursor: not-allowed;
      transform: none;
    }
  </style>
</head>

<body class="font-sans antialiased selection:bg-primary/15 selection:text-primary">
  <!-- HEADER / NAV -->
  <header class="sticky top-0 z-40 border-b border-border/70 backdrop-blur bg-background/80">
    <div class="container mx-auto px-4">
      <div class="flex flex-wrap items-center justify-between gap-4 py-4 md:h-20 md:flex-nowrap">
        <a href="{{ route('home') }}" class="flex items-center gap-3 flex-shrink-0" aria-label="Gramlyze">
          <x-gramlyze-logo class="hidden md:inline-flex" />
          <x-gramlyze-logo variant="compact" class="md:hidden" />
        </a>
        <form action="{{ route('site.search') }}" method="GET" class="relative hidden md:block">
          <input type="search" name="q" id="search-box" autocomplete="off" placeholder="Пошук..." class="w-48 rounded-xl border border-input bg-background px-3 py-2 text-sm" />
          <div id="search-box-list" class="absolute left-0 mt-1 w-full bg-background border border-border rounded-xl shadow-soft text-sm hidden z-50"></div>
        </form>
        <div class="flex items-center gap-2 md:hidden">
          <button id="mobile-search-btn" class="rounded-xl border border-border p-2 text-sm" aria-expanded="false" aria-controls="mobile-search">🔍<span class="sr-only">Пошук</span></button>
          <button id="mobile-menu-toggle" class="rounded-xl border border-border p-2 text-sm" aria-expanded="false" aria-controls="primary-nav">
            <span class="sr-only">Меню</span>
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="4" y1="6" x2="20" y2="6" />
              <line x1="4" y1="12" x2="20" y2="12" />
              <line x1="4" y1="18" x2="20" y2="18" />
            </svg>
          </button>
        </div>
        <nav id="primary-nav" class="order-3 hidden flex w-full flex-col gap-3 border-t border-border/70 pt-3 text-sm font-medium md:order-none md:flex md:w-auto md:flex-row md:items-center md:gap-6 md:border-0 md:pt-0">
          <a class="text-muted-foreground transition hover:text-foreground" href="{{ route('catalog-tests.cards') }}">Каталог</a>
          <a class="text-muted-foreground transition hover:text-foreground" href="{{ route('pages.index') }}">Теорія</a>
          <a class="text-muted-foreground transition hover:text-foreground" href="{{ route('question-review.index') }}">Рецензії</a>
          <a class="text-muted-foreground transition hover:text-foreground" href="#ai-toolkit">AI Toolkit</a>
          <a class="text-muted-foreground transition hover:text-foreground" href="#team-collaboration">Командам</a>
        </nav>
      </div>
      <div id="mobile-search" class="md:hidden hidden pb-3">
        <form action="{{ route('site.search') }}" method="GET" class="relative">
          <input type="search" name="q" id="search-box-mobile" autocomplete="off" placeholder="Пошук..." class="mt-3 w-full rounded-xl border border-input bg-background px-3 py-2 text-sm" />
          <div id="search-box-mobile-list" class="absolute left-0 right-0 mt-1 bg-background border border-border rounded-xl shadow-soft text-sm hidden z-50"></div>
        </form>
      </div>
    </div>
  </header>

  <main class="container mx-auto px-4 py-8">
    @yield('content')
  </main>

  <footer class="border-t border-border mt-10 py-6 text-sm">
    <div class="container mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-4">
      <div class="flex items-center gap-2">
        <x-gramlyze-logo variant="compact" size="h-9 w-9" />
        <span>Gramlyze <span id="year"></span></span>
      </div>
      <div class="flex md:justify-end gap-4 text-sm">
        <a class="text-muted-foreground hover:text-foreground" href="#">Політика</a>
        <a class="text-muted-foreground hover:text-foreground" href="#">Умови</a>
        <a class="text-muted-foreground hover:text-foreground" href="#faq">Підтримка</a>
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
        if (window.matchMedia('(max-width: 767px)').matches) {
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

    // Animate on scroll
    const animated = document.querySelectorAll('[data-animate]');
    if (animated.length) {
      const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add('animate-in');
            if (entry.target.dataset.animateOnce !== 'false') {
              observer.unobserve(entry.target);
            }
          }
        });
      }, { threshold: 0.15 });

      animated.forEach((el) => {
        const delay = el.dataset.animateDelay;
        if (delay) {
          el.style.setProperty('--animate-delay', delay);
        }
        observer.observe(el);
      });
    }

    // Mobile sliders
    document.querySelectorAll('[data-slider]').forEach((slider) => {
      const track = slider.querySelector('[data-slider-track]');
      if (!track) return;
      const prev = slider.querySelector('[data-slider-prev]');
      const next = slider.querySelector('[data-slider-next]');

      const getGap = () => {
        const styles = window.getComputedStyle(track);
        const gapValue = parseFloat(styles.columnGap || styles.gap || '0');
        return Number.isFinite(gapValue) ? gapValue : 0;
      };

      const getScrollAmount = () => {
        const firstSlide = track.querySelector('[data-slide]');
        if (!firstSlide) return track.clientWidth;
        const slideRect = firstSlide.getBoundingClientRect();
        return slideRect.width + getGap();
      };

      const updateButtons = () => {
        if (!prev || !next) return;
        const maxScroll = track.scrollWidth - track.clientWidth - 1;
        prev.disabled = track.scrollLeft <= 0;
        next.disabled = track.scrollLeft >= maxScroll;
      };

      const scrollByAmount = (direction) => {
        track.scrollBy({ left: direction * getScrollAmount(), behavior: 'smooth' });
      };

      prev?.addEventListener('click', () => {
        scrollByAmount(-1);
      });
      next?.addEventListener('click', () => {
        scrollByAmount(1);
      });

      track.addEventListener('scroll', () => {
        window.requestAnimationFrame(updateButtons);
      });

      window.addEventListener('resize', updateButtons);
      updateButtons();
    });
  </script>

  @yield('scripts')
</body>
</html>
