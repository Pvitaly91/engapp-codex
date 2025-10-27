<!doctype html>
<html lang="uk" class="h-full">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ $title ?? 'Engram — Попередній перегляд сторінки' }}</title>

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">

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
    <script src="//unpkg.com/alpinejs" defer></script>

    <style>
        :root {
            --background: 0 0% 100%;
            --foreground: 15 7% 11%;
            --card: 0 0% 100%;
            --card-foreground: 15 7% 11%;
            --popover: 0 0% 100%;
            --popover-foreground: 15 7% 11%;
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

        html, body {
            height: 100%;
        }

        body {
            background: hsl(var(--background));
            color: hsl(var(--foreground));
        }

        .container {
            max-width: 72rem;
        }
    </style>
</head>
<body class="font-sans antialiased selection:bg-primary/15 selection:text-primary bg-background">
    <main class="container mx-auto px-4 py-8">
        {!! $content !!}
    </main>

    @if(!empty($scripts))
        {!! $scripts !!}
    @endif

    @if(isset($stacks) && $stacks instanceof \Illuminate\Support\Collection)
        @foreach($stacks as $stack)
            {!! $stack !!}
        @endforeach
    @endif
</body>
</html>
