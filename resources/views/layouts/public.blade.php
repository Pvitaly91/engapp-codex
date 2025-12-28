<!doctype html>
<html lang="{{ app()->getLocale() }}" class="h-full">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>@yield('title', __('public.meta.title'))</title>
  <meta name="description" content="{{ __('public.meta.description') }}" />

  <!-- Google Font: Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Tailwind CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="//unpkg.com/alpinejs" defer></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          fontFamily: { 
            sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'] 
          },
          colors: {
            border: 'hsl(var(--border))',
            input: 'hsl(var(--input))',
            ring: 'hsl(var(--ring))',
            background: 'hsl(var(--background))',
            foreground: 'hsl(var(--foreground))',
            primary: { 
              DEFAULT: 'hsl(var(--primary))', 
              foreground: 'hsl(var(--primary-foreground))' 
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
            popover: { 
              DEFAULT: 'hsl(var(--popover))', 
              foreground: 'hsl(var(--popover-foreground))' 
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
            xl: '0.75rem', 
            '2xl': '1rem', 
            '3xl': '1.5rem' 
          },
          boxShadow: { 
            soft: '0 4px 20px -4px rgba(0,0,0,0.1)',
            glow: '0 0 20px -5px hsla(var(--primary), 0.3)',
          }
        }
      }
    }
  </script>

  <!-- Design tokens (light/dark) -->
  <style>
    :root {
      --background: 0 0% 100%;
      --foreground: 224 71% 4%;
      --card: 0 0% 100%;
      --card-foreground: 224 71% 4%;
      --popover: 0 0% 100%;
      --popover-foreground: 224 71% 4%;
      --primary: 262 83% 58%;
      --primary-foreground: 0 0% 100%;
      --secondary: 220 14% 96%;
      --secondary-foreground: 220 9% 46%;
      --muted: 220 14% 96%;
      --muted-foreground: 220 9% 46%;
      --accent: 262 83% 58%;
      --accent-foreground: 0 0% 100%;
      --destructive: 0 84% 60%;
      --destructive-foreground: 0 0% 100%;
      --success: 142 76% 36%;
      --warning: 38 92% 50%;
      --info: 217 91% 60%;
      --border: 220 13% 91%;
      --input: 220 13% 91%;
      --ring: 262 83% 58%;
    }
    
    .dark {
      --background: 224 71% 4%;
      --foreground: 213 31% 91%;
      --card: 224 71% 4%;
      --card-foreground: 213 31% 91%;
      --popover: 224 71% 4%;
      --popover-foreground: 213 31% 91%;
      --primary: 263 70% 50%;
      --primary-foreground: 0 0% 100%;
      --secondary: 215 28% 17%;
      --secondary-foreground: 213 31% 91%;
      --muted: 223 47% 11%;
      --muted-foreground: 215 16% 57%;
      --accent: 263 70% 50%;
      --accent-foreground: 0 0% 100%;
      --destructive: 0 63% 31%;
      --destructive-foreground: 213 31% 91%;
      --success: 142 55% 45%;
      --warning: 38 90% 55%;
      --info: 217 80% 65%;
      --border: 216 34% 17%;
      --input: 216 34% 17%;
      --ring: 263 70% 50%;
    }
    
    html, body { 
      height: 100%; 
    }
    
    body { 
      background: hsl(var(--background)); 
      color: hsl(var(--foreground)); 
    }
    
    .page-container { 
      max-width: 80rem; 
    }

    /* Smooth animations */
    [x-cloak] { display: none !important; }
    
    .animate-fade-in {
      animation: fadeIn 0.2s ease-out;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-4px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    /* Focus ring styles */
    .focus-ring {
      @apply focus:outline-none focus:ring-2 focus:ring-primary/50 focus:ring-offset-2 focus:ring-offset-background;
    }
    
    /* Custom scrollbar for dropdowns */
    .scrollbar-thin::-webkit-scrollbar {
      width: 6px;
    }
    .scrollbar-thin::-webkit-scrollbar-track {
      background: transparent;
    }
    .scrollbar-thin::-webkit-scrollbar-thumb {
      background: hsl(var(--border));
      border-radius: 3px;
    }
    .scrollbar-thin::-webkit-scrollbar-thumb:hover {
      background: hsl(var(--muted-foreground));
    }
    .scrollbar-thin {
      scrollbar-width: thin;
      scrollbar-color: hsl(var(--border)) transparent;
    }
  </style>
  @livewireStyles
</head>

<body class="font-sans antialiased min-h-full flex flex-col">
  <!-- Header -->
  @include('components.public.header')

  <!-- Main Content -->
  <main class="flex-1 page-container mx-auto w-full px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
    @yield('content')
  </main>

  <!-- Footer -->
  @include('components.public.footer')

  <!-- Global Scripts -->
  <script>
    // Dark mode toggle with persistence
    (function initTheme() {
      const saved = localStorage.getItem('theme');
      const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
      if (saved === 'dark' || (!saved && systemDark)) {
        document.documentElement.classList.add('dark');
      }
    })();
    
    function toggleTheme() {
      document.documentElement.classList.toggle('dark');
      const isDark = document.documentElement.classList.contains('dark');
      localStorage.setItem('theme', isDark ? 'dark' : 'light');
    }
  </script>

  @livewireScripts
  @yield('scripts')
</body>
</html>
