<!doctype html>
<html lang="uk" class="h-full">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>@yield('title', 'Engram — Вивчення англійської')</title>
  <meta name="description" content="Короткі тести з англійської, проста теорія, прогрес та рекомендації." />

  <!-- Google Font: Montserrat -->
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">

  <!-- Tailwind CDN + runtime config -->
  <script src="https://cdn.tailwindcss.com"></script>
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
  </style>
</head>

<body class="font-sans antialiased selection:bg-primary/15 selection:text-primary">
  <!-- HEADER / NAV -->
  <header class="sticky top-0 z-40 border-b border-border/70 backdrop-blur bg-background/80">
    <div class="container mx-auto px-4">
      <div class="flex h-16 items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="h-9 w-9 rounded-2xl bg-primary text-primary-foreground grid place-items-center font-bold">E</div>
          <span class="text-lg font-semibold tracking-tight">Engram</span>
          <span class="ml-2 inline-flex items-center rounded-lg bg-accent text-accent-foreground px-2 py-0.5 text-xs font-medium">beta</span>
        </div>
        <nav class="hidden md:flex items-center gap-6 text-sm">
          <a class="text-muted-foreground hover:text-foreground" href="#tests">Тести</a>
          <a class="text-muted-foreground hover:text-foreground" href="#theory">Теорія</a>
          <a class="text-muted-foreground hover:text-foreground" href="#quiz">Швидкий тест</a>
          <a class="text-muted-foreground hover:text-foreground" href="#faq">FAQ</a>
        </nav>
        <div class="flex items-center gap-2">
          <button id="theme-toggle" class="hidden sm:inline-flex rounded-xl border border-border px-3 py-2 text-sm">
            🌙 Тема
          </button>
          <button class="rounded-2xl bg-primary px-4 py-2 text-primary-foreground text-sm">Реєстрація</button>
        </div>
      </div>
    </div>
  </header>

  <main class="container mx-auto px-4 py-8">
    @yield('content')
  </main>

  <footer class="border-t border-border mt-10 py-6 text-sm">
    <div class="container mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-4">
      <div class="flex items-center gap-2">
        <div class="h-6 w-6 rounded-md bg-primary text-primary-foreground grid place-items-center font-semibold text-xs">E</div>
        <span>Engram <span id="year"></span></span>
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
  </script>

  @yield('scripts')
</body>
</html>
