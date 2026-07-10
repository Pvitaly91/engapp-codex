<!doctype html>
<html lang="{{ app()->getLocale() }}" class="h-full" x-data="themeController()" x-init="init()" x-bind:class="{ 'dark': isDark }" x-bind:data-background-mode="backgroundMode" x-bind:style="customBackgroundStyle()">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', __('public.meta.title'))</title>
    <meta name="description" content="@yield('meta_description', __('public.meta.description'))" />
    @include('layouts.partials.social-meta')
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@600;700;800&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="//unpkg.com/alpinejs" defer></script>
    <script>
        (() => {
            try {
                const mode = localStorage.getItem('backgroundMode');
                if (['cards', 'custom'].includes(mode)) {
                    document.documentElement.dataset.backgroundMode = mode;
                }

                const defaults = {
                    ct: '#2f67b1',
                    cr: '#4b55e8',
                    th: '#f99a24',
                    wd: '#172033',
                    vb: '#12b982',
                };
                const savedColors = JSON.parse(localStorage.getItem('backgroundColors') || '{}');
                Object.entries(defaults).forEach(([key, fallback]) => {
                    const value = /^#[0-9a-f]{6}$/i.test(savedColors[key] || '') ? savedColors[key] : fallback;
                    document.documentElement.style.setProperty(`--app-custom-${key}`, value);
                });
            } catch (error) {
                document.documentElement.dataset.backgroundMode = 'blue';
            }
        })();
    </script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            600: '#4350e6',
                            700: '#3730a3'
                        },
                        steel: '#5d7185',
                        night: '#13233b',
                        ocean: '#2f67b1',
                        amber: '#f59b2f',
                        mist: '#f5fbff',
                        shell: '#fffefd',
                        line: '#d8e2ee'
                    },
                    fontFamily: {
                        display: ['Archivo', 'sans-serif'],
                        body: ['Manrope', 'sans-serif']
                    },
                    boxShadow: {
                        panel: '0 24px 60px rgba(17, 38, 63, 0.18)',
                        card: '0 12px 28px rgba(17, 38, 63, 0.10)'
                    }
                }
            }
        };
    </script>
    <style>
        :root {
            --app-bg: linear-gradient(180deg, #607487 0%, #526678 100%);
            --shell-bg: #f5fbff;
            --shell-border: rgba(255, 255, 255, 0.72);
            --shell-radius: 30px;
            --surface: rgba(255, 254, 253, 0.94);
            --surface-strong: #ffffff;
            --line: #d8e2ee;
            --text: #14233b;
            --muted: #5b6c80;
            --accent: #2f67b1;
            --accent-soft: rgba(47, 103, 177, 0.10);
            --footer-bg: linear-gradient(180deg, #13233b 0%, #0f1b31 100%);
        }

        .dark {
            --app-bg: linear-gradient(180deg, #0b1730 0%, #14233b 100%);
            --shell-bg: #0f1c30;
            --shell-border: rgba(111, 140, 172, 0.34);
            --surface: rgba(15, 29, 49, 0.90);
            --surface-strong: #16253d;
            --line: rgba(127, 153, 182, 0.26);
            --text: #edf5ff;
            --muted: #b5c5d6;
            --accent: #74a9f0;
            --accent-soft: rgba(116, 169, 240, 0.16);
            --footer-bg: linear-gradient(180deg, #091425 0%, #0b1528 100%);
        }

        [x-cloak] { display: none !important; }

        body {
            margin: 0;
            background: var(--app-bg);
            color: var(--text);
        }

        .app-fixed-background {
            --app-bg-icon-primary: #2563eb;
            --app-bg-icon-strong: #1d4ed8;
            --app-bg-icon-soft: #93c5fd;
            --app-bg-chat-from: #dff7ff;
            --app-bg-chat-to: #dbeafe;
            --app-bg-doc-from: #dfe7ff;
            --app-bg-doc-to: #cfdcff;
            --app-bg-check-from: #38d5ff;
            --app-bg-check-to: #2563eb;
            position: fixed;
            inset: 0;
            pointer-events: none;
            background:
                radial-gradient(circle at 50% 45%, rgba(255,255,255,.99) 0 19%, rgba(244,250,255,.96) 38%, rgba(209,236,255,.84) 64%, rgba(160,214,255,.58) 86%, transparent 100%),
                radial-gradient(circle at 0% 14%, rgba(23,184,255,.38), transparent 34%),
                radial-gradient(circle at 100% 8%, rgba(37,140,255,.48), transparent 36%),
                radial-gradient(circle at 4% 98%, rgba(0,132,255,.52), transparent 34%),
                radial-gradient(circle at 96% 92%, rgba(0,178,255,.30), transparent 30%),
                linear-gradient(135deg, #e4f6ff 0%, #fbfdff 42%, #d8efff 100%);
            transform: translateZ(0);
            overflow: hidden;
        }

        [data-background-mode="cards"] .app-fixed-background {
            --app-bg-icon-primary: #2f67b1;
            --app-bg-icon-strong: #1f4d8f;
            --app-bg-icon-soft: rgba(75, 85, 232, .42);
            --app-bg-chat-from: #eef4ff;
            --app-bg-chat-to: #dbe9fb;
            --app-bg-doc-from: #e8edf7;
            --app-bg-doc-to: #d5deee;
            --app-bg-check-from: #12b982;
            --app-bg-check-to: #2f67b1;
            background:
                radial-gradient(circle at 50% 45%, rgba(255,255,255,.99) 0 18%, rgba(250,252,255,.97) 37%, rgba(239,245,252,.88) 60%, transparent 100%),
                radial-gradient(circle at 12% 17%, rgba(47,103,177,.38), transparent 30%),
                radial-gradient(circle at 32% 14%, rgba(75,85,232,.30), transparent 28%),
                radial-gradient(circle at 52% 15%, rgba(249,154,36,.34), transparent 27%),
                radial-gradient(circle at 72% 16%, rgba(23,32,51,.24), transparent 28%),
                radial-gradient(circle at 90% 17%, rgba(18,185,130,.34), transparent 28%),
                radial-gradient(circle at 8% 96%, rgba(47,103,177,.28), transparent 32%),
                radial-gradient(circle at 92% 90%, rgba(18,185,130,.24), transparent 30%),
                linear-gradient(135deg, #edf4fb 0%, #fffaf3 42%, #effaf5 100%);
        }

        [data-background-mode="custom"] .app-fixed-background {
            --app-bg-icon-primary: var(--app-custom-ct, #2f67b1);
            --app-bg-icon-strong: color-mix(in srgb, var(--app-custom-ct, #2f67b1) 76%, #172033);
            --app-bg-icon-soft: color-mix(in srgb, var(--app-custom-cr, #4b55e8) 42%, transparent);
            --app-bg-chat-from: color-mix(in srgb, var(--app-custom-ct, #2f67b1) 14%, #ffffff);
            --app-bg-chat-to: color-mix(in srgb, var(--app-custom-cr, #4b55e8) 18%, #ffffff);
            --app-bg-doc-from: color-mix(in srgb, var(--app-custom-wd, #172033) 9%, #ffffff);
            --app-bg-doc-to: color-mix(in srgb, var(--app-custom-wd, #172033) 18%, #ffffff);
            --app-bg-check-from: var(--app-custom-vb, #12b982);
            --app-bg-check-to: var(--app-custom-ct, #2f67b1);
            background:
                radial-gradient(circle at 50% 45%, rgba(255,255,255,.99) 0 18%, rgba(250,252,255,.97) 37%, color-mix(in srgb, var(--app-custom-ct, #2f67b1) 6%, #eff5fc) 60%, transparent 100%),
                radial-gradient(circle at 12% 17%, color-mix(in srgb, var(--app-custom-ct, #2f67b1) 38%, transparent), transparent 30%),
                radial-gradient(circle at 32% 14%, color-mix(in srgb, var(--app-custom-cr, #4b55e8) 32%, transparent), transparent 28%),
                radial-gradient(circle at 52% 15%, color-mix(in srgb, var(--app-custom-th, #f99a24) 36%, transparent), transparent 27%),
                radial-gradient(circle at 72% 16%, color-mix(in srgb, var(--app-custom-wd, #172033) 26%, transparent), transparent 28%),
                radial-gradient(circle at 90% 17%, color-mix(in srgb, var(--app-custom-vb, #12b982) 34%, transparent), transparent 28%),
                radial-gradient(circle at 8% 96%, color-mix(in srgb, var(--app-custom-ct, #2f67b1) 28%, transparent), transparent 32%),
                radial-gradient(circle at 92% 90%, color-mix(in srgb, var(--app-custom-vb, #12b982) 26%, transparent), transparent 30%),
                linear-gradient(135deg,
                    color-mix(in srgb, var(--app-custom-ct, #2f67b1) 9%, #f9fcff) 0%,
                    color-mix(in srgb, var(--app-custom-th, #f99a24) 10%, #ffffff) 43%,
                    color-mix(in srgb, var(--app-custom-vb, #12b982) 10%, #f8fffb) 100%);
        }

        .app-fixed-background::before {
            content: "";
            position: absolute;
            inset: 0;
            background: url('{{ asset('gramlyze-background.svg') }}') center / cover no-repeat;
            opacity: 1;
            animation: gramlyze-background-float 9s ease-in-out infinite;
        }

        [data-background-mode="cards"] .app-fixed-background::before {
            background: url('{{ asset('gramlyze-background-cards.svg') }}') center / cover no-repeat;
        }

        [data-background-mode="custom"] .app-fixed-background::before {
            background: none;
        }

        .app-fixed-background::after {
            content: "";
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 50% 50%, rgba(255,255,255,.38), rgba(255,255,255,.12) 44%, transparent 76%);
            inset: 10% 23%;
        }

        [data-background-mode="cards"] .app-fixed-background::after {
            background: radial-gradient(circle at 50% 50%, rgba(255,255,255,.48), rgba(255,255,255,.20) 46%, transparent 76%);
        }

        [data-background-mode="custom"] .app-fixed-background::after {
            background: radial-gradient(circle at 50% 50%, rgba(255,255,255,.48), rgba(255,255,255,.20) 46%, transparent 76%);
        }

        .app-bg-vector {
            position: absolute;
            inset: 0;
            z-index: 1;
            width: 100%;
            height: 100%;
            opacity: 0;
            pointer-events: none;
            animation: gramlyze-background-float 9s ease-in-out infinite;
        }

        [data-background-mode="custom"] .app-bg-vector {
            opacity: 1;
        }

        .app-bg-icon {
            position: absolute;
            z-index: 2;
            transform: scale(var(--random-scale, 1));
            transform-origin: center;
            will-change: top, left, right, transform;
        }

        .app-bg-icon svg {
            display: block;
            width: 100%;
            height: auto;
            overflow: visible;
        }

        .app-bg-icon-inner {
            animation: gramlyze-icon-float-y 7s ease-in-out infinite;
            will-change: transform;
        }

        .app-bg-icon--slow .app-bg-icon-inner { animation-duration: 9s; }
        .app-bg-icon--fast .app-bg-icon-inner { animation-duration: 5.4s; }
        .app-bg-icon--shadow { filter: drop-shadow(0 18px 22px rgba(29, 78, 216, .13)); }

        .app-bg-icon--chat { width: clamp(145px, 15vw, 230px); left: 4.7vw; top: 7vh; }
        .app-bg-icon--aa { width: clamp(90px, 8vw, 125px); left: 4.5vw; top: 38vh; opacity: .40; }
        .app-bg-icon--comma { width: clamp(58px, 5.2vw, 84px); left: 5.5vw; top: 59vh; opacity: .44; }
        .app-bg-icon--dots-left { width: clamp(115px, 10vw, 155px); left: 4.9vw; top: 73vh; opacity: .31; }
        .app-bg-icon--ring-left { width: clamp(34px, 3vw, 48px); left: 26vw; top: 88vh; opacity: .26; }
        .app-bg-icon--quote { width: clamp(74px, 6.5vw, 108px); right: 18vw; top: 4.5vh; opacity: .46; }
        .app-bg-icon--dots-right { width: clamp(120px, 11vw, 168px); right: 14vw; top: 13vh; opacity: .29; }
        .app-bg-icon--ring-right { width: clamp(36px, 3.2vw, 52px); right: 9vw; top: 16vh; opacity: .36; }
        .app-bg-icon--document { width: clamp(96px, 9vw, 138px); right: 5.2vw; top: 31vh; opacity: .70; }
        .app-bg-icon--braces { width: clamp(92px, 8vw, 130px); right: 5.8vw; top: 59vh; opacity: .43; }
        .app-bg-icon--check { width: clamp(110px, 10vw, 150px); right: 8vw; top: 78vh; }
        .app-bg-icon--dots-low { width: clamp(95px, 8vw, 130px); right: 18vw; top: 76vh; opacity: .19; }

        [data-background-mode="cards"] .app-bg-icon--chat {
            --app-bg-icon-strong: #2f67b1;
            --app-bg-chat-from: #edf5ff;
            --app-bg-chat-to: #dbe8f8;
        }

        [data-background-mode="cards"] .app-bg-icon--aa {
            --app-bg-icon-primary: #4b55e8;
            --app-bg-icon-soft: rgba(75, 85, 232, .34);
        }

        [data-background-mode="cards"] .app-bg-icon--comma,
        [data-background-mode="cards"] .app-bg-icon--quote {
            --app-bg-icon-primary: #f99a24;
        }

        [data-background-mode="cards"] .app-bg-icon--dots-left,
        [data-background-mode="cards"] .app-bg-icon--check {
            --app-bg-icon-primary: #12b982;
        }

        [data-background-mode="cards"] .app-bg-icon--ring-left,
        [data-background-mode="cards"] .app-bg-icon--dots-right {
            --app-bg-icon-primary: #4b55e8;
        }

        [data-background-mode="cards"] .app-bg-icon--ring-right,
        [data-background-mode="cards"] .app-bg-icon--braces,
        [data-background-mode="cards"] .app-bg-icon--document {
            --app-bg-icon-primary: #172033;
            --app-bg-doc-from: #eef2f7;
            --app-bg-doc-to: #d8e0ec;
        }

        [data-background-mode="cards"] .app-bg-icon--dots-low {
            --app-bg-icon-primary: #2f67b1;
        }

        [data-background-mode="cards"] .app-bg-icon--check {
            --app-bg-check-from: #12b982;
            --app-bg-check-to: #2f67b1;
        }

        [data-background-mode="custom"] .app-bg-icon--chat {
            --app-bg-icon-strong: var(--app-custom-ct, #2f67b1);
            --app-bg-chat-from: color-mix(in srgb, var(--app-custom-ct, #2f67b1) 14%, #ffffff);
            --app-bg-chat-to: color-mix(in srgb, var(--app-custom-cr, #4b55e8) 16%, #ffffff);
        }

        [data-background-mode="custom"] .app-bg-icon--aa,
        [data-background-mode="custom"] .app-bg-icon--ring-left,
        [data-background-mode="custom"] .app-bg-icon--dots-right {
            --app-bg-icon-primary: var(--app-custom-cr, #4b55e8);
            --app-bg-icon-soft: color-mix(in srgb, var(--app-custom-cr, #4b55e8) 34%, transparent);
        }

        [data-background-mode="custom"] .app-bg-icon--comma,
        [data-background-mode="custom"] .app-bg-icon--quote {
            --app-bg-icon-primary: var(--app-custom-th, #f99a24);
        }

        [data-background-mode="custom"] .app-bg-icon--ring-right,
        [data-background-mode="custom"] .app-bg-icon--braces,
        [data-background-mode="custom"] .app-bg-icon--document {
            --app-bg-icon-primary: var(--app-custom-wd, #172033);
            --app-bg-doc-from: color-mix(in srgb, var(--app-custom-wd, #172033) 9%, #ffffff);
            --app-bg-doc-to: color-mix(in srgb, var(--app-custom-wd, #172033) 18%, #ffffff);
        }

        [data-background-mode="custom"] .app-bg-icon--dots-left,
        [data-background-mode="custom"] .app-bg-icon--check {
            --app-bg-icon-primary: var(--app-custom-vb, #12b982);
        }

        [data-background-mode="custom"] .app-bg-icon--dots-low {
            --app-bg-icon-primary: var(--app-custom-ct, #2f67b1);
        }

        [data-background-mode="custom"] .app-bg-icon--check {
            --app-bg-check-from: var(--app-custom-vb, #12b982);
            --app-bg-check-to: var(--app-custom-ct, #2f67b1);
        }

        .dark .app-fixed-background::before {
            opacity: 0.22;
            filter: brightness(.75) saturate(.8);
        }

        .dark .app-fixed-background::after {
            background: radial-gradient(circle, rgba(15,28,48,.08), rgba(15,28,48,.26) 76%);
        }

        @keyframes gramlyze-background-float {
            0%, 100% { transform: translateY(0) scale(1.01); }
            50% { transform: translateY(-8px) scale(1.01); }
        }

        @keyframes gramlyze-icon-float-y {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        @media (prefers-reduced-motion: reduce) {
            .app-fixed-background::before { animation: none; }
            .app-bg-vector { animation: none; }
            .app-bg-icon-inner { animation: none; }
        }

        .catalog-shell {
            position: relative;
            background:
                radial-gradient(circle at top, rgba(104, 165, 231, 0.10), transparent 24%),
                linear-gradient(180deg, var(--shell-bg) 0%, color-mix(in srgb, var(--shell-bg) 92%, white) 100%);
            border-color: var(--shell-border);
            border-radius: var(--shell-radius);
        }

        .site-header {
            position: sticky;
            top: 0;
            z-index: 40;
            isolation: isolate;
            background: transparent !important;
            border-top-left-radius: var(--shell-radius);
            border-top-right-radius: var(--shell-radius);
            contain: none;
            overflow: visible;
            transform: translateZ(0);
            backface-visibility: hidden;
            transition: border-top-left-radius 160ms ease, border-top-right-radius 160ms ease, box-shadow 160ms ease, border-color 160ms ease;
        }

        .site-header > div {
            overflow: visible;
        }

        .site-header::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: -1;
            pointer-events: none;
            background: color-mix(in srgb, var(--surface-strong) 94%, var(--surface));
            border-top-left-radius: inherit;
            border-top-right-radius: inherit;
            transition: opacity 160ms ease, background-color 160ms ease;
            will-change: opacity;
        }

        .catalog-shell.is-header-stuck .site-header {
            border-top-left-radius: 0 !important;
            border-top-right-radius: 0 !important;
            box-shadow: 0 16px 36px rgba(17, 38, 63, 0.12);
        }

        .catalog-shell.is-header-stuck .site-header::before {
            background: color-mix(in srgb, var(--surface-strong) 98%, var(--surface));
        }

        .surface-card {
            background: var(--surface);
            border-color: var(--line);
        }

        .surface-card-strong {
            background: var(--surface-strong);
            border-color: var(--line);
        }

        .soft-accent {
            background: var(--accent-soft);
        }

        .footer-shell {
            background: var(--footer-bg);
            border-bottom-left-radius: var(--shell-radius);
            border-bottom-right-radius: var(--shell-radius);
            overflow: hidden;
        }

        .catalog-shell > main,
        .catalog-shell > footer {
            position: relative;
            z-index: 1;
        }

        #shell-random-shapes {
            position: absolute;
            inset: 0;
            z-index: 0;
        }

        #shell-random-shapes > span {
            will-change: transform, opacity;
        }

        .decorated-content .surface-card {
            background: color-mix(in srgb, var(--surface) 92%, var(--surface-strong));
        }

        .decorated-content .surface-card-strong {
            background: color-mix(in srgb, var(--surface-strong) 96%, var(--surface));
        }

        .decorated-content .soft-accent {
            background: color-mix(in srgb, var(--accent-soft) 84%, var(--surface-strong));
        }

        body.scroll-optimized .theory-lazy-section {
            content-visibility: auto;
            contain-intrinsic-size: auto 720px;
        }

        body.scroll-optimized .decorated-content .surface-card {
            background: color-mix(in srgb, var(--surface) 96%, var(--surface-strong));
        }

        body.scroll-optimized .decorated-content .surface-card-strong {
            background: color-mix(in srgb, var(--surface-strong) 98%, var(--surface));
        }

        body.scroll-optimized .decorated-content .soft-accent {
            background: color-mix(in srgb, var(--accent-soft) 90%, var(--surface-strong));
        }

        .nd-page {
            overflow: hidden;
            padding-inline: 0.75rem;
            padding-block: 2rem;
        }

        .nd-section {
            padding-inline: 0;
            padding-block: 2.5rem;
        }

        .nd-section-tight {
            padding-inline: 0;
            padding-block: 2rem;
        }

        .catalog-frame {
            width: 100%;
        }

        @media (max-width: 1023px) {
            body {
                background: var(--shell-bg);
                overflow-x: hidden;
            }

            .app-fixed-background {
                display: none;
            }

            .catalog-frame {
                width: 100vw !important;
                max-width: 100vw !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .catalog-shell {
                width: 100vw !important;
                min-height: 100dvh;
                margin: 0 !important;
                border: 0 !important;
                border-radius: 0 !important;
                box-shadow: none !important;
            }

            .site-header,
            .site-header::before,
            .footer-shell {
                border-radius: 0 !important;
            }

            .site-header {
                width: 100% !important;
                padding-inline: 0 !important;
            }

            .site-header > div {
                padding-inline: 1rem;
            }

            .nd-page {
                width: 100% !important;
                overflow: visible !important;
                padding-inline: 0 !important;
                padding-block: 1rem 1.25rem !important;
            }

            main {
                overflow: visible;
            }
        }

        @media (min-width: 640px) {
            .nd-page {
                padding-inline: 1.25rem;
            }

            .nd-page {
                padding-block: 2.5rem;
            }

            .nd-section {
                padding-block: 2.85rem;
            }

            .nd-section-tight {
                padding-block: 2.35rem;
            }
        }

        @media (min-width: 1024px) {
            .nd-page {
                padding-inline: 1.5rem;
            }

            .nd-page {
                padding-block: 2.75rem;
            }

            .nd-section {
                padding-block: 3.25rem;
            }

            .nd-section-tight {
                padding-block: 2.6rem;
            }
        }
    </style>
    @livewireStyles
    @yield('head')
</head>
<body class="min-h-full font-body antialiased @yield('body_class')">
    @include('components.admin-domain-switcher')
    @php
        $backgroundWidgetVisible = (bool) session('admin_authenticated', false)
            || (bool) session('admin_user_id', false)
            || (bool) data_get(auth()->user(), 'is_admin', false);
    @endphp
    <div class="app-fixed-background" aria-hidden="true">
        <svg class="app-bg-vector" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 900" preserveAspectRatio="xMidYMid slice">
            <defs>
                <linearGradient id="appCustomTopCards" x1="0" y1="0" x2="1" y2="1">
                    <stop stop-color="var(--app-custom-cr, #4b55e8)"/>
                    <stop offset=".34" stop-color="var(--app-custom-ct, #2f67b1)"/>
                    <stop offset=".68" stop-color="var(--app-custom-vb, #12b982)"/>
                    <stop offset="1" stop-color="var(--app-custom-th, #f99a24)"/>
                </linearGradient>
                <linearGradient id="appCustomBottomCards" x1="0" y1="0" x2="1" y2="1">
                    <stop stop-color="var(--app-custom-th, #f99a24)"/>
                    <stop offset=".45" stop-color="var(--app-custom-ct, #2f67b1)"/>
                    <stop offset="1" stop-color="var(--app-custom-wd, #172033)"/>
                </linearGradient>
                <linearGradient id="appCustomBottomSoftCards" x1="0" y1="0" x2="1" y2="1">
                    <stop stop-color="color-mix(in srgb, var(--app-custom-th, #f99a24) 28%, #ffffff)"/>
                    <stop offset=".45" stop-color="color-mix(in srgb, var(--app-custom-ct, #2f67b1) 24%, #ffffff)"/>
                    <stop offset="1" stop-color="color-mix(in srgb, var(--app-custom-vb, #12b982) 24%, #ffffff)"/>
                </linearGradient>
            </defs>

            <path d="M1010 0h430v360c-42-38-79-82-122-119-58-50-116-69-186-88-82-22-128-65-146-153z" fill="url(#appCustomTopCards)" opacity=".92"/>
            <path d="M1124 114c78 18 145 43 204 96 36 32 72 66 112 100v50c-42-38-79-82-122-119-58-50-116-69-186-88-82-22-128-65-146-153h86c14 51 30 88 52 114z" fill="#ffffff" opacity=".20"/>
            <path d="M1184 78c82 16 168 55 256 128v68c-74-70-152-112-234-130-60-13-104-36-132-66z" fill="var(--app-custom-wd, #172033)" opacity=".10"/>

            <path d="M0 668c90 12 156 44 214 84 52 36 120 63 166 63 45 1 84-21 120-53v138H0z" fill="url(#appCustomBottomSoftCards)" opacity=".84"/>
            <path d="M0 711c84 10 145 45 205 83 61 38 118 66 182 58 43-5 80-27 113-54v102H0z" fill="url(#appCustomBottomCards)" opacity=".94"/>

            <g fill="none" stroke-linecap="round" stroke-dasharray="9 14" opacity=".34">
                <path d="M180 165c95-25 155 45 230 15" stroke="var(--app-custom-ct, #2f67b1)" stroke-width="3"/>
                <path d="M1060 155c120-60 238-15 352 65" stroke="var(--app-custom-cr, #4b55e8)" stroke-width="3"/>
                <path d="M165 840c120-50 215 70 335 45" stroke="var(--app-custom-th, #f99a24)" stroke-width="3"/>
                <path d="M1040 815c120-50 220 40 380 25" stroke="var(--app-custom-vb, #12b982)" stroke-width="3"/>
            </g>

            <g opacity=".20">
                <circle cx="122" cy="122" r="46" fill="var(--app-custom-ct, #2f67b1)"/>
                <circle cx="306" cy="92" r="34" fill="var(--app-custom-cr, #4b55e8)"/>
                <circle cx="540" cy="142" r="42" fill="var(--app-custom-th, #f99a24)"/>
                <circle cx="910" cy="124" r="38" fill="var(--app-custom-wd, #172033)"/>
                <circle cx="1260" cy="604" r="48" fill="var(--app-custom-vb, #12b982)"/>
            </g>
        </svg>

        <div class="app-bg-icon app-bg-icon--chat app-bg-icon--shadow" data-app-bg-icon="chat" data-app-bg-side="left">
            <div class="app-bg-icon-inner">
                <svg viewBox="0 0 220 170" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="appChatGradient" x1="0" y1="0" x2="1" y2="1">
                            <stop offset="0%" stop-color="var(--app-bg-chat-from, #dff7ff)"/>
                            <stop offset="100%" stop-color="var(--app-bg-chat-to, #dbeafe)"/>
                        </linearGradient>
                    </defs>
                    <path d="M0 37C0 15 15 0 37 0h145c22 0 37 15 37 37v65c0 22-15 37-37 37H78l-39 30c-8 6-18 1-17-9l3-22C10 130 0 118 0 102V37Z" fill="url(#appChatGradient)" opacity=".92"/>
                    <circle cx="73" cy="69" r="10.5" fill="var(--app-bg-icon-strong, #1d4ed8)" opacity=".85"/>
                    <circle cx="109" cy="69" r="10.5" fill="var(--app-bg-icon-strong, #1d4ed8)" opacity=".85"/>
                    <circle cx="146" cy="69" r="10.5" fill="var(--app-bg-icon-strong, #1d4ed8)" opacity=".85"/>
                </svg>
            </div>
        </div>

        <div class="app-bg-icon app-bg-icon--aa app-bg-icon--slow" data-app-bg-icon="aa" data-app-bg-side="left">
            <div class="app-bg-icon-inner">
                <svg viewBox="0 0 125 112" xmlns="http://www.w3.org/2000/svg">
                    <text x="0" y="74" font-family="Georgia, serif" font-size="82" fill="var(--app-bg-icon-primary, #2563eb)">Aa</text>
                    <line x1="0" y1="95" x2="96" y2="95" stroke="var(--app-bg-icon-soft, #93c5fd)" stroke-width="5" stroke-linecap="round"/>
                </svg>
            </div>
        </div>

        <div class="app-bg-icon app-bg-icon--comma app-bg-icon--fast" data-app-bg-icon="comma" data-app-bg-side="left">
            <div class="app-bg-icon-inner">
                <svg viewBox="0 0 90 120" xmlns="http://www.w3.org/2000/svg">
                    <text x="0" y="95" font-family="Georgia, serif" font-size="120" fill="var(--app-bg-icon-primary, #2563eb)">,</text>
                </svg>
            </div>
        </div>

        <div class="app-bg-icon app-bg-icon--dots-left" data-app-bg-icon="dots" data-app-bg-side="left">
            <div class="app-bg-icon-inner">
                <svg viewBox="0 0 150 90" xmlns="http://www.w3.org/2000/svg" fill="var(--app-bg-icon-primary, #3b82f6)">
                    <circle cx="0" cy="0" r="4"/><circle cx="30" cy="0" r="4"/><circle cx="60" cy="0" r="4"/><circle cx="90" cy="0" r="4"/><circle cx="120" cy="0" r="4"/>
                    <circle cx="0" cy="30" r="4"/><circle cx="30" cy="30" r="4"/><circle cx="60" cy="30" r="4"/><circle cx="90" cy="30" r="4"/><circle cx="120" cy="30" r="4"/>
                    <circle cx="0" cy="60" r="4"/><circle cx="30" cy="60" r="4"/><circle cx="60" cy="60" r="4"/><circle cx="90" cy="60" r="4"/><circle cx="120" cy="60" r="4"/>
                </svg>
            </div>
        </div>

        <div class="app-bg-icon app-bg-icon--ring-left app-bg-icon--slow" data-app-bg-icon="ring" data-app-bg-side="left">
            <div class="app-bg-icon-inner">
                <svg viewBox="0 0 50 50" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="25" cy="25" r="18" fill="none" stroke="var(--app-bg-icon-primary, #60a5fa)" stroke-width="5"/>
                </svg>
            </div>
        </div>

        <div class="app-bg-icon app-bg-icon--quote app-bg-icon--fast" data-app-bg-icon="quote" data-app-bg-side="right">
            <div class="app-bg-icon-inner">
                <svg viewBox="0 0 110 100" xmlns="http://www.w3.org/2000/svg">
                    <text x="0" y="82" font-family="Georgia, serif" font-size="104" font-weight="700" fill="var(--app-bg-icon-primary, #2563eb)">“</text>
                </svg>
            </div>
        </div>

        <div class="app-bg-icon app-bg-icon--dots-right" data-app-bg-icon="dots" data-app-bg-side="right">
            <div class="app-bg-icon-inner">
                <svg viewBox="0 0 150 90" xmlns="http://www.w3.org/2000/svg" fill="var(--app-bg-icon-primary, #3b82f6)">
                    <circle cx="0" cy="0" r="4"/><circle cx="30" cy="0" r="4"/><circle cx="60" cy="0" r="4"/><circle cx="90" cy="0" r="4"/><circle cx="120" cy="0" r="4"/>
                    <circle cx="0" cy="30" r="4"/><circle cx="30" cy="30" r="4"/><circle cx="60" cy="30" r="4"/><circle cx="90" cy="30" r="4"/><circle cx="120" cy="30" r="4"/>
                    <circle cx="0" cy="60" r="4"/><circle cx="30" cy="60" r="4"/><circle cx="60" cy="60" r="4"/><circle cx="90" cy="60" r="4"/><circle cx="120" cy="60" r="4"/>
                </svg>
            </div>
        </div>

        <div class="app-bg-icon app-bg-icon--ring-right app-bg-icon--fast" data-app-bg-icon="ring" data-app-bg-side="right">
            <div class="app-bg-icon-inner">
                <svg viewBox="0 0 50 50" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="25" cy="25" r="18" fill="none" stroke="var(--app-bg-icon-primary, #60a5fa)" stroke-width="5"/>
                </svg>
            </div>
        </div>

        <div class="app-bg-icon app-bg-icon--document app-bg-icon--shadow" data-app-bg-icon="document" data-app-bg-side="right">
            <div class="app-bg-icon-inner">
                <svg viewBox="0 0 132 140" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="appDocGradient" x1="0" y1="0" x2="1" y2="1">
                            <stop offset="0%" stop-color="var(--app-bg-doc-from, #dfe7ff)"/>
                            <stop offset="100%" stop-color="var(--app-bg-doc-to, #cfdcff)"/>
                        </linearGradient>
                    </defs>
                    <rect width="132" height="140" rx="22" fill="url(#appDocGradient)"/>
                    <path d="M30 48h78M30 70h78M30 92h58" stroke="var(--app-bg-icon-primary, #2563eb)" stroke-width="6" stroke-linecap="round"/>
                </svg>
            </div>
        </div>

        <div class="app-bg-icon app-bg-icon--braces app-bg-icon--slow" data-app-bg-icon="braces" data-app-bg-side="right">
            <div class="app-bg-icon-inner">
                <svg viewBox="0 0 130 100" xmlns="http://www.w3.org/2000/svg">
                    <text x="0" y="77" font-family="ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace" font-size="80" fill="var(--app-bg-icon-primary, #2563eb)">{ }</text>
                </svg>
            </div>
        </div>

        <div class="app-bg-icon app-bg-icon--dots-low app-bg-icon--fast" data-app-bg-icon="dots-low" data-app-bg-side="right">
            <div class="app-bg-icon-inner">
                <svg viewBox="0 0 120 90" xmlns="http://www.w3.org/2000/svg" fill="var(--app-bg-icon-primary, #3b82f6)">
                    <circle cx="0" cy="0" r="4"/><circle cx="30" cy="0" r="4"/><circle cx="60" cy="0" r="4"/><circle cx="90" cy="0" r="4"/>
                    <circle cx="0" cy="30" r="4"/><circle cx="30" cy="30" r="4"/><circle cx="60" cy="30" r="4"/><circle cx="90" cy="30" r="4"/>
                    <circle cx="0" cy="60" r="4"/><circle cx="30" cy="60" r="4"/><circle cx="60" cy="60" r="4"/><circle cx="90" cy="60" r="4"/>
                </svg>
            </div>
        </div>

        <div class="app-bg-icon app-bg-icon--check app-bg-icon--shadow" data-app-bg-icon="check" data-app-bg-side="right">
            <div class="app-bg-icon-inner">
                <svg viewBox="0 0 150 150" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="appCheckGradient" x1="0" y1="0" x2="1" y2="1">
                            <stop offset="0%" stop-color="var(--app-bg-check-from, #38d5ff)"/>
                            <stop offset="100%" stop-color="var(--app-bg-check-to, #2563eb)"/>
                        </linearGradient>
                    </defs>
                    <circle cx="75" cy="75" r="70" fill="url(#appCheckGradient)" opacity=".88"/>
                    <path d="M42 75l24 25 46-55" fill="none" stroke="#fff" stroke-width="12" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
        </div>
    </div>
    <div class="catalog-frame relative mx-auto max-w-[1440px] px-0 py-0 lg:px-8 lg:py-6">
        <div id="catalog-shell" class="catalog-shell rounded-none border-0 shadow-none lg:rounded-[30px] lg:border lg:shadow-panel">
            <div id="shell-random-shapes" class="pointer-events-none" aria-hidden="true"></div>
            @yield('shell_background')
            <div id="site-header-sentinel" class="pointer-events-none absolute inset-x-0 top-0 h-px" aria-hidden="true"></div>
            <header id="site-header" class="site-header border-b px-0 py-4 lg:px-8 surface-card" style="border-color: var(--line);">
                <div class="flex flex-col gap-4 px-4 xl:flex-row xl:items-center xl:justify-between lg:px-0">
                    <div class="flex items-center justify-between gap-4">
                        <a href="{{ localized_route('home') }}" class="flex items-center gap-3" aria-label="Gramlyze">
                            <x-gramlyze-logo variant="compact" class="h-12 w-12 rounded-2xl bg-[linear-gradient(135deg,#f7b34c_0%,#fff0d7_50%,#2f67b1_100%)] shadow-card" />
                            <div>
                                <p class="font-display text-xl font-extrabold leading-none tracking-tight">GRAMLYZE</p>
                                <p class="mt-1 text-xs font-semibold uppercase tracking-[0.28em]" style="color: var(--muted);">{{ __('public.home.badge') }}</p>
                            </div>
                        </a>

                        <button @click="mobile = !mobile" class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border xl:hidden surface-card-strong" aria-label="{{ __('public.nav.menu') }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>

                    <nav class="hidden items-center gap-6 text-[15px] font-semibold xl:flex">
                        @devMode
                            <a href="{{ localized_route('catalog.tests-cards') }}" class="transition hover:text-ocean">{{ __('public.nav.catalog') }}</a>
                        @enddevMode
                        <a href="{{ localized_route('theory.index') }}" class="transition hover:text-ocean">{{ __('public.nav.theory') }}</a>
                        <a href="{{ localized_route('courses.index') }}" class="transition hover:text-ocean">{{ __('public.nav.courses') }}</a>
                        <a href="{{ localized_route('words.test') }}" class="transition hover:text-ocean">{{ __('public.nav.words_test') }}</a>
                        <a href="{{ localized_route('verbs.test') }}" class="transition hover:text-ocean">{{ __('public.nav.verbs_test') }}</a>
                    </nav>

                    <div class="hidden items-center gap-3 xl:flex">
                        <div x-data="searchBox()" class="relative">
                            <form @submit.prevent="go">
                                <input x-model="query" @input="autocomplete" @keydown.escape="open = false" type="search" placeholder="{{ __('public.search.placeholder') }}" class="w-72 rounded-2xl border px-4 py-3 text-sm font-medium shadow-sm outline-none transition focus:border-ocean focus:ring-2 focus:ring-blue-100 surface-card-strong">
                            </form>
                            <div x-show="open" x-cloak x-transition class="absolute left-0 right-0 top-full z-30 mt-2 max-h-[min(28rem,calc(100vh-8rem))] overflow-y-auto overscroll-contain rounded-[22px] border shadow-card surface-card-strong">
                                <template x-if="!results.length">
                                    <div class="px-4 py-3 text-sm" style="color: var(--muted);">{{ __('public.search.nothing_found') }}</div>
                                </template>
                                <template x-for="item in results" :key="item.url">
                                    <a :href="item.url" class="block border-b px-4 py-3 last:border-b-0 hover:bg-blue-50/80 dark:hover:bg-slate-800/70" style="border-color: var(--line);">
                                        <p class="font-semibold" x-html="highlight(item.title)"></p>
                                        <p class="text-xs" style="color: var(--muted);" x-text="item.url"></p>
                                    </a>
                                </template>
                            </div>
                        </div>

                        <div x-data="languageSwitcher()" class="relative">
                            <button @click="toggle" :aria-expanded="open" class="flex items-center gap-3 rounded-2xl border px-3 py-2.5 text-sm font-semibold shadow-sm transition hover:border-ocean surface-card-strong">
                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl soft-accent text-xs font-bold" style="color: var(--accent);" x-text="active.code.toUpperCase()"></span>
                                <span class="max-w-24 truncate" x-text="active.localized_name || active.native_name || active.name"></span>
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="open" x-cloak @click.outside="open = false" x-transition class="absolute right-0 z-30 mt-2 w-72 overflow-hidden rounded-[22px] border shadow-card surface-card-strong">
                                <div class="border-b p-3" style="border-color: var(--line);">
                                    <input x-model="filter" type="search" placeholder="{{ __('public.language.search') }}" class="w-full rounded-2xl border px-3 py-2 text-sm outline-none transition focus:border-ocean focus:ring-2 focus:ring-blue-100 surface-card">
                                </div>
                                <div class="max-h-64 overflow-y-auto">
                                    <template x-for="lang in filtered" :key="lang.code">
                                        <a :href="lang.url" @click="open = false" class="flex items-center justify-between gap-3 border-b px-4 py-3 last:border-b-0 hover:bg-blue-50/80 dark:hover:bg-slate-800/70" style="border-color: var(--line);">
                                            <div class="flex items-center gap-3">
                                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl soft-accent text-xs font-bold" style="color: var(--accent);" x-text="lang.code.toUpperCase()"></span>
                                                <div>
                                                    <p class="text-sm font-semibold" x-text="lang.localized_name || lang.native_name || lang.name"></p>
                                                    <p class="text-xs" style="color: var(--muted);" x-text="lang.name"></p>
                                                </div>
                                            </div>
                                            <span x-show="lang.is_current" x-cloak class="text-xs font-semibold" style="color: var(--accent);">{{ __('public.language.current') }}</span>
                                        </a>
                                    </template>
                                </div>
                            </div>
                        </div>

                        @if($backgroundWidgetVisible)
                            <div class="relative" @click.outside="backgroundPanelOpen = false">
                                <button data-testid="background-mode-toggle-desktop" @click="toggleBackgroundPanel" :aria-expanded="backgroundPanelOpen" class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border shadow-sm transition hover:border-ocean surface-card-strong" :aria-label="'{{ __('public.background.widget') }}'">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" aria-hidden="true">
                                        <circle cx="7" cy="8" r="3.3" fill="var(--app-custom-ct, #2f67b1)" />
                                        <circle cx="13.6" cy="6.4" r="3.3" fill="var(--app-custom-cr, #4b55e8)" />
                                        <circle cx="17" cy="12.4" r="3.3" fill="var(--app-custom-th, #f99a24)" />
                                        <circle cx="11.1" cy="17.2" r="3.3" fill="var(--app-custom-vb, #12b982)" />
                                        <circle cx="6.6" cy="14.2" r="3.3" fill="var(--app-custom-wd, #172033)" />
                                    </svg>
                                </button>

                                <div x-show="backgroundPanelOpen" x-cloak x-transition class="absolute right-0 top-full z-50 mt-2 w-80 rounded-[24px] border p-4 shadow-card surface-card-strong">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="text-sm font-extrabold">{{ __('public.background.title') }}</p>
                                            <p class="mt-1 text-xs" style="color: var(--muted);">{{ __('public.background.description') }}</p>
                                        </div>
                                        <button type="button" @click="backgroundPanelOpen = false" class="rounded-xl px-2 py-1 text-xs font-bold soft-accent" style="color: var(--accent);">{{ __('public.background.close') }}</button>
                                    </div>

                                    <div class="mt-4 grid grid-cols-3 gap-2">
                                        <button data-testid="background-mode-blue-desktop" type="button" @click="setBackgroundMode('blue')" class="rounded-2xl border px-3 py-2 text-left text-xs font-bold transition surface-card" :style="backgroundMode === 'blue' ? 'border-color: var(--accent); box-shadow: 0 0 0 2px rgba(47,103,177,.14)' : ''">
                                            <span class="block h-4 rounded-full bg-gradient-to-r from-sky-300 to-blue-600"></span>
                                            <span class="mt-2 block">{{ __('public.background.blue_short') }}</span>
                                        </button>
                                        <button data-testid="background-mode-cards-desktop" type="button" @click="setBackgroundMode('cards')" class="rounded-2xl border px-3 py-2 text-left text-xs font-bold transition surface-card" :style="backgroundMode === 'cards' ? 'border-color: var(--accent); box-shadow: 0 0 0 2px rgba(47,103,177,.14)' : ''">
                                            <span class="block h-4 rounded-full" style="background: linear-gradient(90deg, #2f67b1, #4b55e8, #f99a24, #172033, #12b982);"></span>
                                            <span class="mt-2 block">{{ __('public.background.cards_short') }}</span>
                                        </button>
                                        <button data-testid="background-mode-custom-desktop" type="button" @click="setBackgroundMode('custom')" class="rounded-2xl border px-3 py-2 text-left text-xs font-bold transition surface-card" :style="backgroundMode === 'custom' ? 'border-color: var(--accent); box-shadow: 0 0 0 2px rgba(47,103,177,.14)' : ''">
                                            <span class="block h-4 rounded-full" :style="customPreviewGradient()"></span>
                                            <span class="mt-2 block">{{ __('public.background.custom_short') }}</span>
                                        </button>
                                    </div>

                                    <div x-show="backgroundMode === 'custom'" x-cloak class="mt-4 space-y-3">
                                        <template x-for="control in backgroundColorControls" :key="control.key">
                                            <label class="grid grid-cols-[auto_1fr_auto] items-center gap-3 rounded-2xl border p-2 surface-card" style="border-color: var(--line);">
                                                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl text-xs font-extrabold text-white" :style="`background: ${safeBackgroundColor(control.key)}`" x-text="control.short"></span>
                                                <span>
                                                    <span class="block text-xs font-bold" x-text="control.label"></span>
                                                    <input :data-testid="`background-hex-${control.key}`" x-model="backgroundColors[control.key]" @input="saveBackgroundColors" class="mt-1 w-full rounded-xl border px-2 py-1 text-xs font-semibold uppercase outline-none surface-card-strong" style="border-color: var(--line);" maxlength="7">
                                                </span>
                                                <input :data-testid="`background-color-${control.key}`" type="color" x-model="backgroundColors[control.key]" @input="saveBackgroundColors" class="h-9 w-11 cursor-pointer rounded-xl border bg-transparent p-1" style="border-color: var(--line);">
                                            </label>
                                        </template>
                                        <button type="button" @click="resetBackgroundColors" class="w-full rounded-2xl border px-3 py-2 text-xs font-bold transition hover:border-ocean surface-card">
                                            {{ __('public.background.reset') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <button @click="toggleTheme" class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border shadow-sm transition hover:border-ocean surface-card-strong" :aria-label="isDark ? '{{ __('public.theme.light') }}' : '{{ __('public.theme.dark') }}'">
                            <template x-if="isDark">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
                                </svg>
                            </template>
                            <template x-if="!isDark">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="5" />
                                    <path d="M12 1v2m0 18v2m11-11h-2M3 12H1m17.657-7.657l-1.414 1.414M6.757 17.243l-1.414 1.414m0-13.414l1.414 1.414M17.243 17.243l1.414 1.414" />
                                </svg>
                            </template>
                        </button>
                    </div>
                </div>

                <div id="site-header-test-controls" class="px-4 lg:px-0"></div>

                <div x-show="mobile" x-cloak x-transition class="mt-4 space-y-4 border-t pt-4 xl:hidden" style="border-color: var(--line);">
                    <nav class="grid gap-2 text-sm font-semibold">
                        @devMode
                            <a href="{{ localized_route('catalog.tests-cards') }}" class="rounded-2xl border px-4 py-3 transition hover:border-ocean surface-card-strong">{{ __('public.nav.catalog') }}</a>
                        @enddevMode
                        <a href="{{ localized_route('theory.index') }}" class="rounded-2xl border px-4 py-3 transition hover:border-ocean surface-card-strong">{{ __('public.nav.theory') }}</a>
                        <a href="{{ localized_route('courses.index') }}" class="rounded-2xl border px-4 py-3 transition hover:border-ocean surface-card-strong">{{ __('public.nav.courses') }}</a>
                        <a href="{{ localized_route('words.test') }}" class="rounded-2xl border px-4 py-3 transition hover:border-ocean surface-card-strong">{{ __('public.nav.words_test') }}</a>
                        <a href="{{ localized_route('verbs.test') }}" class="rounded-2xl border px-4 py-3 transition hover:border-ocean surface-card-strong">{{ __('public.nav.verbs_test') }}</a>
                    </nav>

                    <div class="grid gap-3 md:grid-cols-[1fr_auto_auto_auto]">
                        <div x-data="searchBox()" class="relative">
                            <form @submit.prevent="go">
                                <input x-model="query" @input="autocomplete" @keydown.escape="open = false" type="search" placeholder="{{ __('public.search.placeholder') }}" class="w-full rounded-2xl border px-4 py-3 text-sm font-medium outline-none transition focus:border-ocean focus:ring-2 focus:ring-blue-100 surface-card-strong">
                            </form>
                            <div x-show="open" x-cloak x-transition class="absolute left-0 right-0 top-full z-20 mt-2 max-h-[min(24rem,calc(100vh-10rem))] overflow-y-auto overscroll-contain rounded-[22px] border shadow-card surface-card-strong">
                                <template x-for="item in results" :key="item.url">
                                    <a :href="item.url" class="block border-b px-4 py-3 last:border-b-0 hover:bg-blue-50/80 dark:hover:bg-slate-800/70" style="border-color: var(--line);">
                                        <p class="font-semibold" x-html="highlight(item.title)"></p>
                                        <p class="text-xs" style="color: var(--muted);" x-text="item.url"></p>
                                    </a>
                                </template>
                            </div>
                        </div>

                        <div x-data="languageSwitcher()" class="relative">
                            <button @click="toggle" class="flex h-full items-center gap-2 rounded-2xl border px-4 py-3 text-sm font-semibold surface-card-strong">
                                <span x-text="active.code.toUpperCase()"></span>
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="open" x-cloak @click.outside="open = false" class="absolute left-0 right-0 z-20 mt-2 overflow-hidden rounded-[22px] border shadow-card surface-card-strong">
                                <template x-for="lang in filtered" :key="lang.code">
                                    <a :href="lang.url" class="block border-b px-4 py-3 text-sm last:border-b-0 hover:bg-blue-50/80 dark:hover:bg-slate-800/70" style="border-color: var(--line);" x-text="lang.localized_name || lang.native_name || lang.name"></a>
                                </template>
                            </div>
                        </div>

                        <button @click="toggleTheme" class="inline-flex h-full items-center justify-center rounded-2xl border px-4 py-3 surface-card-strong" :aria-label="isDark ? '{{ __('public.theme.light') }}' : '{{ __('public.theme.dark') }}'">
                            <span x-show="!isDark" x-cloak>☾</span>
                            <span x-show="isDark" x-cloak>☼</span>
                        </button>

                        @if($backgroundWidgetVisible)
                            <button data-testid="background-mode-toggle-mobile" @click="toggleBackgroundPanel" class="inline-flex h-full items-center justify-center rounded-2xl border px-4 py-3 surface-card-strong" :aria-label="'{{ __('public.background.widget') }}'">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" aria-hidden="true">
                                    <circle cx="7" cy="8" r="3.3" fill="var(--app-custom-ct, #2f67b1)" />
                                    <circle cx="13.6" cy="6.4" r="3.3" fill="var(--app-custom-cr, #4b55e8)" />
                                    <circle cx="17" cy="12.4" r="3.3" fill="var(--app-custom-th, #f99a24)" />
                                    <circle cx="11.1" cy="17.2" r="3.3" fill="var(--app-custom-vb, #12b982)" />
                                    <circle cx="6.6" cy="14.2" r="3.3" fill="var(--app-custom-wd, #172033)" />
                                </svg>
                            </button>
                        @endif
                    </div>

                    @if($backgroundWidgetVisible)
                        <div x-show="backgroundPanelOpen" x-cloak x-transition class="rounded-[24px] border p-4 shadow-card surface-card-strong">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-extrabold">{{ __('public.background.title') }}</p>
                                    <p class="mt-1 text-xs" style="color: var(--muted);">{{ __('public.background.description') }}</p>
                                </div>
                                <button type="button" @click="backgroundPanelOpen = false" class="rounded-xl px-2 py-1 text-xs font-bold soft-accent" style="color: var(--accent);">{{ __('public.background.close') }}</button>
                            </div>

                            <div class="mt-4 grid grid-cols-3 gap-2">
                                <button data-testid="background-mode-blue-mobile" type="button" @click="setBackgroundMode('blue')" class="rounded-2xl border px-3 py-2 text-left text-xs font-bold transition surface-card" :style="backgroundMode === 'blue' ? 'border-color: var(--accent); box-shadow: 0 0 0 2px rgba(47,103,177,.14)' : ''">
                                    <span class="block h-4 rounded-full bg-gradient-to-r from-sky-300 to-blue-600"></span>
                                    <span class="mt-2 block">{{ __('public.background.blue_short') }}</span>
                                </button>
                                <button data-testid="background-mode-cards-mobile" type="button" @click="setBackgroundMode('cards')" class="rounded-2xl border px-3 py-2 text-left text-xs font-bold transition surface-card" :style="backgroundMode === 'cards' ? 'border-color: var(--accent); box-shadow: 0 0 0 2px rgba(47,103,177,.14)' : ''">
                                    <span class="block h-4 rounded-full" style="background: linear-gradient(90deg, #2f67b1, #4b55e8, #f99a24, #172033, #12b982);"></span>
                                    <span class="mt-2 block">{{ __('public.background.cards_short') }}</span>
                                </button>
                                <button data-testid="background-mode-custom-mobile" type="button" @click="setBackgroundMode('custom')" class="rounded-2xl border px-3 py-2 text-left text-xs font-bold transition surface-card" :style="backgroundMode === 'custom' ? 'border-color: var(--accent); box-shadow: 0 0 0 2px rgba(47,103,177,.14)' : ''">
                                    <span class="block h-4 rounded-full" :style="customPreviewGradient()"></span>
                                    <span class="mt-2 block">{{ __('public.background.custom_short') }}</span>
                                </button>
                            </div>

                            <div x-show="backgroundMode === 'custom'" x-cloak class="mt-4 space-y-3">
                                <template x-for="control in backgroundColorControls" :key="control.key">
                                    <label class="grid grid-cols-[auto_1fr_auto] items-center gap-3 rounded-2xl border p-2 surface-card" style="border-color: var(--line);">
                                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl text-xs font-extrabold text-white" :style="`background: ${safeBackgroundColor(control.key)}`" x-text="control.short"></span>
                                        <span>
                                            <span class="block text-xs font-bold" x-text="control.label"></span>
                                            <input :data-testid="`background-mobile-hex-${control.key}`" x-model="backgroundColors[control.key]" @input="saveBackgroundColors" class="mt-1 w-full rounded-xl border px-2 py-1 text-xs font-semibold uppercase outline-none surface-card-strong" style="border-color: var(--line);" maxlength="7">
                                        </span>
                                        <input :data-testid="`background-mobile-color-${control.key}`" type="color" x-model="backgroundColors[control.key]" @input="saveBackgroundColors" class="h-9 w-11 cursor-pointer rounded-xl border bg-transparent p-1" style="border-color: var(--line);">
                                    </label>
                                </template>
                                <button type="button" @click="resetBackgroundColors" class="w-full rounded-2xl border px-3 py-2 text-xs font-bold transition hover:border-ocean surface-card">
                                    {{ __('public.background.reset') }}
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </header>

            <main class="decorated-content px-4 lg:px-0">
                @yield('content')
            </main>

            <footer class="footer-shell relative overflow-hidden px-5 py-8 text-white sm:px-8 lg:px-10">
                <div class="grid gap-8 lg:grid-cols-[1.1fr_0.9fr_0.9fr]">
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <x-gramlyze-logo variant="compact" class="h-12 w-12 rounded-2xl bg-[linear-gradient(135deg,#f7b34c_0%,#fff0d7_50%,#2f67b1_100%)] shadow-card" />
                            <div>
                                <p class="font-display text-xl font-extrabold leading-none tracking-tight">GRAMLYZE</p>
                                <p class="mt-1 text-sm text-white/70">{{ __('public.footer.description') }}</p>
                            </div>
                        </div>
                        <p class="text-sm text-white/70">© <span x-text="new Date().getFullYear()"></span> Gramlyze</p>
                    </div>

                    <div>
                        <h3 class="font-display text-lg font-extrabold">{{ __('public.footer.links') }}</h3>
                        <div class="mt-4 grid gap-3 text-sm text-white/80">
                            @devMode
                                <a href="{{ localized_route('catalog.tests-cards') }}" class="transition hover:text-white">{{ __('public.nav.catalog') }}</a>
                            @enddevMode
                            <a href="{{ localized_route('theory.index') }}" class="transition hover:text-white">{{ __('public.nav.theory') }}</a>
                            <a href="{{ localized_route('courses.index') }}" class="transition hover:text-white">{{ __('public.nav.courses') }}</a>
                            <a href="{{ localized_route('words.test') }}" class="transition hover:text-white">{{ __('public.nav.words_test') }}</a>
                            <a href="{{ localized_route('verbs.test') }}" class="transition hover:text-white">{{ __('public.nav.verbs_test') }}</a>
                        </div>
                    </div>

                    <div>
                        <h3 class="font-display text-lg font-extrabold">{{ __('public.footer.contact') }}</h3>
                        <p class="mt-4 text-sm text-white/80">{{ __('public.footer.support') }}</p>
                        <div class="mt-5 flex flex-wrap gap-3">
                            <a href="{{ localized_route('site.search') }}" class="rounded-2xl bg-white/10 px-4 py-3 text-sm font-semibold transition hover:bg-white/20">{{ __('public.search.button') }}</a>
                            <button @click="toggleTheme" class="rounded-2xl bg-white/10 px-4 py-3 text-sm font-semibold transition hover:bg-white/20">{{ __('public.footer.theme') }}</button>
                        </div>
                    </div>
                </div>

            </footer>
        </div>
    </div>
 
    <script>
        function themeController() {
            return {
                isDark: false,
                mobile: false,
                backgroundPanelOpen: false,
                backgroundMode: 'blue',
                backgroundColors: {
                    ct: '#2f67b1',
                    cr: '#4b55e8',
                    th: '#f99a24',
                    wd: '#172033',
                    vb: '#12b982',
                },
                backgroundColorControls: [
                    { key: 'ct', short: 'CT', label: @js(__('public.background.ct')) },
                    { key: 'cr', short: 'CR', label: @js(__('public.background.cr')) },
                    { key: 'th', short: 'TH', label: @js(__('public.background.th')) },
                    { key: 'wd', short: 'WD', label: @js(__('public.background.wd')) },
                    { key: 'vb', short: 'VB', label: @js(__('public.background.vb')) },
                ],
                init() {
                    const saved = localStorage.getItem('theme');
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    this.isDark = saved ? saved === 'dark' : prefersDark;
                    const savedBackgroundMode = localStorage.getItem('backgroundMode');
                    this.backgroundMode = ['blue', 'cards', 'custom'].includes(savedBackgroundMode) ? savedBackgroundMode : 'blue';
                    this.backgroundColors = this.loadBackgroundColors();
                },
                toggleTheme() {
                    this.isDark = !this.isDark;
                    localStorage.setItem('theme', this.isDark ? 'dark' : 'light');
                },
                defaultBackgroundColors() {
                    return {
                        ct: '#2f67b1',
                        cr: '#4b55e8',
                        th: '#f99a24',
                        wd: '#172033',
                        vb: '#12b982',
                    };
                },
                isHexColor(value) {
                    return /^#[0-9a-f]{6}$/i.test(value || '');
                },
                loadBackgroundColors() {
                    const defaults = this.defaultBackgroundColors();

                    try {
                        const saved = JSON.parse(localStorage.getItem('backgroundColors') || '{}');

                        return Object.fromEntries(Object.entries(defaults).map(([key, fallback]) => [
                            key,
                            this.isHexColor(saved[key]) ? saved[key] : fallback,
                        ]));
                    } catch (error) {
                        return defaults;
                    }
                },
                safeBackgroundColor(key) {
                    const defaults = this.defaultBackgroundColors();
                    const value = this.backgroundColors[key];

                    return this.isHexColor(value) ? value : defaults[key];
                },
                customBackgroundStyle() {
                    return Object.keys(this.defaultBackgroundColors())
                        .map((key) => `--app-custom-${key}: ${this.safeBackgroundColor(key)}`)
                        .join('; ');
                },
                customPreviewGradient() {
                    return `background: linear-gradient(90deg, ${this.safeBackgroundColor('ct')}, ${this.safeBackgroundColor('cr')}, ${this.safeBackgroundColor('th')}, ${this.safeBackgroundColor('wd')}, ${this.safeBackgroundColor('vb')});`;
                },
                saveBackgroundColors() {
                    const colors = Object.fromEntries(Object.keys(this.defaultBackgroundColors()).map((key) => [
                        key,
                        this.safeBackgroundColor(key),
                    ]));

                    localStorage.setItem('backgroundColors', JSON.stringify(colors));
                },
                resetBackgroundColors() {
                    this.backgroundColors = this.defaultBackgroundColors();
                    this.saveBackgroundColors();
                },
                toggleBackgroundPanel() {
                    this.backgroundPanelOpen = !this.backgroundPanelOpen;
                },
                setBackgroundMode(mode) {
                    if (!['blue', 'cards', 'custom'].includes(mode)) {
                        return;
                    }

                    this.backgroundMode = mode;
                    localStorage.setItem('backgroundMode', mode);
                    this.refreshBackgroundDecorations();
                },
                toggleBackground() {
                    const nextMode = this.backgroundMode === 'blue'
                        ? 'cards'
                        : (this.backgroundMode === 'cards' ? 'custom' : 'blue');

                    this.setBackgroundMode(nextMode);
                },
                refreshBackgroundDecorations() {
                    window.requestAnimationFrame(() => {
                        if (typeof randomizeAppBackgroundIcons === 'function') {
                            randomizeAppBackgroundIcons();
                        }
                    });
                }
            }
        }

        function searchBox() {
            return {
                query: '',
                results: [],
                open: false,
                go() {
                    if (this.query.trim()) {
                        window.location.href = '{{ localized_route('site.search') }}?q=' + encodeURIComponent(this.query.trim());
                    }
                },
                highlight(text) {
                    const safe = (text || '').replace(/[&<>"]/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[char]));
                    if (!this.query) {
                        return safe;
                    }

                    const regex = new RegExp('(' + this.query.replace(/[-/\\^$*+?.()|[\]{}]/g, '\\$&') + ')', 'ig');

                    return safe.replace(regex, '<mark class="rounded bg-amber-200/80 px-1 text-slate-900">$1</mark>');
                },
                async autocomplete() {
                    if (this.query.trim().length < 2) {
                        this.results = [];
                        this.open = false;
                        return;
                    }

                    const response = await fetch('{{ localized_route('site.search') }}?q=' + encodeURIComponent(this.query.trim()), {
                        headers: { 'Accept': 'application/json' }
                    });

                    this.results = await response.json();
                    this.open = true;
                }
            }
        }

        function languageSwitcher() {
            return {
                open: false,
                filter: '',
                languages: @json($__languageSwitcher ?? []),
                get active() {
                    return this.languages.find((lang) => lang.is_current)
                        || this.languages[0]
                        || { code: '{{ app()->getLocale() }}', name: '{{ app()->getLocale() }}' };
                },
                toggle() {
                    this.open = !this.open;
                },
                get filtered() {
                    const term = this.filter.toLowerCase();

                    return this.languages.filter((lang) => (
                        !term
                        || (lang.localized_name && lang.localized_name.toLowerCase().includes(term))
                        || (lang.name && lang.name.toLowerCase().includes(term))
                        || (lang.native_name && lang.native_name.toLowerCase().includes(term))
                        || lang.code.toLowerCase().includes(term)
                    ));
                }
            }
        }

        function initStickyShellHeader() {
            const shell = document.getElementById('catalog-shell');
            const sentinel = document.getElementById('site-header-sentinel');
            const desktopQuery = window.matchMedia('(min-width: 1024px)');

            if (!shell || !sentinel) {
                return;
            }

            let observer = null;
            let fallbackBound = false;
            let lastState = null;

            const applyStickyState = (isStuck) => {
                const nextState = desktopQuery.matches && Boolean(isStuck);

                if (lastState === nextState) {
                    return;
                }

                lastState = nextState;
                shell.classList.toggle('is-header-stuck', nextState);
            };

            const syncStickyFallback = () => {
                applyStickyState(Math.round(sentinel.getBoundingClientRect().top) < 0);
            };

            const bindFallback = () => {
                if (fallbackBound) {
                    return;
                }

                fallbackBound = true;

                const requestSync = () => window.requestAnimationFrame(syncStickyFallback);
                requestSync();
                window.addEventListener('scroll', requestSync, { passive: true });
                window.addEventListener('resize', requestSync, { passive: true });
            };

            const connectObserver = () => {
                if (observer) {
                    observer.disconnect();
                    observer = null;
                }

                applyStickyState(false);

                if (!desktopQuery.matches) {
                    return;
                }

                if (typeof IntersectionObserver !== 'function') {
                    bindFallback();
                    return;
                }

                observer = new IntersectionObserver(([entry]) => {
                    applyStickyState(!entry.isIntersecting && entry.boundingClientRect.top < 0);
                }, {
                    threshold: [0, 1],
                });

                observer.observe(sentinel);
                window.requestAnimationFrame(syncStickyFallback);
            };

            connectObserver();

            if (typeof desktopQuery.addEventListener === 'function') {
                desktopQuery.addEventListener('change', connectObserver);
            } else if (typeof desktopQuery.addListener === 'function') {
                desktopQuery.addListener(connectObserver);
            }
        }

        function buildShellRandomShapes() {
            const layer = document.getElementById('shell-random-shapes');

            if (!layer) {
                return;
            }

            const width = layer.offsetWidth;
            const height = layer.offsetHeight;

            if (!width || !height) {
                return;
            }

            const randomBetween = (min, max) => min + (Math.random() * (max - min));
            const pick = (items) => items[Math.floor(Math.random() * items.length)];
            const shuffle = (items) => {
                const copy = [...items];

                for (let index = copy.length - 1; index > 0; index -= 1) {
                    const swapIndex = Math.floor(Math.random() * (index + 1));
                    [copy[index], copy[swapIndex]] = [copy[swapIndex], copy[index]];
                }

                return copy;
            };
            const clamp = (value, min, max) => Math.min(Math.max(value, min), max);

            const compact = width < 640;
            const medium = width >= 640 && width < 1024;
            const sizeScale = compact ? 0.68 : (medium ? 0.82 : 1);
            const columns = compact ? 3 : (medium ? 4 : (width >= 1280 ? 6 : 5));
            const rows = compact
                ? Math.max(7, Math.min(13, Math.round(height / 150)))
                : Math.max(6, Math.min(10, Math.round(height / 175)));
            const cellWidth = width / columns;
            const cellHeight = height / rows;
            const shapes = [];
            const scaledBetween = (min, max) => randomBetween(min * sizeScale, max * sizeScale);
            const accentBetween = (min, max) => randomBetween(min * sizeScale * 0.58, max * sizeScale * 0.58);

            const shapeFactories = [
                () => {
                    const size = scaledBetween(58, 124);
                    const stroke = scaledBetween(8, 18);
                    return {
                        width: size,
                        height: size,
                        borderRadius: '999px',
                        border: `${stroke}px solid ${pick([
                            'rgba(47, 103, 177, 0.24)',
                            'rgba(116, 169, 240, 0.30)',
                            'rgba(156, 163, 175, 0.24)',
                            'rgba(125, 211, 252, 0.28)',
                        ])}`,
                        background: 'transparent',
                    };
                },
                () => {
                    const width = scaledBetween(72, 152);
                    const height = scaledBetween(14, 34);
                    return {
                        width,
                        height,
                        borderRadius: '999px',
                        background: pick([
                            'rgba(47, 103, 177, 0.18)',
                            'rgba(245, 155, 47, 0.18)',
                            'rgba(16, 185, 129, 0.18)',
                            'rgba(244, 114, 182, 0.14)',
                            'rgba(56, 189, 248, 0.16)',
                        ]),
                    };
                },
                () => {
                    const width = scaledBetween(46, 92);
                    const height = scaledBetween(46, 92);
                    return {
                        width,
                        height,
                        borderRadius: `${scaledBetween(18, 34)}px`,
                        border: `${scaledBetween(6, 12)}px solid ${pick([
                            'rgba(16, 185, 129, 0.24)',
                            'rgba(125, 211, 252, 0.24)',
                            'rgba(217, 70, 239, 0.18)',
                            'rgba(251, 191, 36, 0.22)',
                        ])}`,
                        background: 'transparent',
                    };
                },
                () => {
                    const width = scaledBetween(38, 84);
                    const height = scaledBetween(38, 84);
                    return {
                        width,
                        height,
                        borderRadius: `${scaledBetween(16, 28)}px`,
                        background: pick([
                            'rgba(245, 155, 47, 0.12)',
                            'rgba(16, 185, 129, 0.12)',
                            'rgba(47, 103, 177, 0.10)',
                            'rgba(251, 113, 133, 0.10)',
                        ]),
                    };
                },
                () => {
                    const width = scaledBetween(18, 30);
                    const height = scaledBetween(110, 190);
                    return {
                        width,
                        height,
                        borderRadius: '999px',
                        background: pick([
                            'rgba(245, 155, 47, 0.14)',
                            'rgba(47, 103, 177, 0.14)',
                            'rgba(16, 185, 129, 0.12)',
                        ]),
                    };
                },
                () => {
                    const width = scaledBetween(86, 156);
                    const height = scaledBetween(42, 74);
                    return {
                        width,
                        height,
                        borderRadius: `${scaledBetween(20, 30)}px`,
                        border: `${scaledBetween(8, 12)}px solid ${pick([
                            'rgba(167, 139, 250, 0.18)',
                            'rgba(125, 211, 252, 0.18)',
                            'rgba(47, 103, 177, 0.16)',
                        ])}`,
                        background: 'transparent',
                    };
                },
                () => {
                    const size = scaledBetween(72, 150);
                    return {
                        width: size,
                        height: size,
                        borderRadius: '999px',
                        background: pick([
                            'rgba(47, 103, 177, 0.08)',
                            'rgba(245, 155, 47, 0.08)',
                            'rgba(16, 185, 129, 0.08)',
                        ]),
                        filter: `blur(${scaledBetween(1, 3)}px)`,
                    };
                },
                () => {
                    const width = scaledBetween(62, 118);
                    const height = scaledBetween(54, 104);
                    return {
                        width,
                        height,
                        background: pick([
                            'rgba(245, 155, 47, 0.14)',
                            'rgba(47, 103, 177, 0.14)',
                            'rgba(16, 185, 129, 0.14)',
                            'rgba(244, 114, 182, 0.12)',
                        ]),
                        clipPath: 'polygon(50% 0%, 0% 100%, 100% 100%)',
                    };
                },
                () => {
                    const size = scaledBetween(54, 96);
                    return {
                        width: size,
                        height: size,
                        background: pick([
                            'rgba(125, 211, 252, 0.16)',
                            'rgba(167, 139, 250, 0.14)',
                            'rgba(251, 191, 36, 0.14)',
                            'rgba(16, 185, 129, 0.14)',
                        ]),
                        clipPath: 'polygon(50% 0%, 100% 50%, 50% 100%, 0% 50%)',
                    };
                },
                () => {
                    const width = scaledBetween(72, 124);
                    const height = scaledBetween(62, 108);
                    return {
                        width,
                        height,
                        background: pick([
                            'rgba(47, 103, 177, 0.12)',
                            'rgba(245, 155, 47, 0.12)',
                            'rgba(16, 185, 129, 0.12)',
                            'rgba(217, 70, 239, 0.10)',
                        ]),
                        clipPath: 'polygon(25% 0%, 75% 0%, 100% 50%, 75% 100%, 25% 100%, 0% 50%)',
                    };
                },
                () => {
                    const size = scaledBetween(52, 92);
                    return {
                        width: size,
                        height: size,
                        background: pick([
                            'rgba(245, 155, 47, 0.16)',
                            'rgba(125, 211, 252, 0.16)',
                            'rgba(16, 185, 129, 0.14)',
                            'rgba(217, 70, 239, 0.12)',
                        ]),
                        clipPath: 'polygon(35% 0%, 65% 0%, 65% 35%, 100% 35%, 100% 65%, 65% 65%, 65% 100%, 35% 100%, 35% 65%, 0% 65%, 0% 35%, 35% 35%)',
                    };
                },
                () => {
                    const width = scaledBetween(64, 120);
                    const height = scaledBetween(58, 110);
                    return {
                        width,
                        height,
                        background: pick([
                            'rgba(47, 103, 177, 0.12)',
                            'rgba(245, 155, 47, 0.12)',
                            'rgba(16, 185, 129, 0.12)',
                            'rgba(251, 113, 133, 0.10)',
                        ]),
                        clipPath: 'polygon(50% 0%, 100% 38%, 82% 100%, 18% 100%, 0% 38%)',
                    };
                },
                () => {
                    const size = scaledBetween(44, 82);
                    return {
                        width: size,
                        height: size,
                        borderRadius: `${scaledBetween(14, 22)}px`,
                        border: `${scaledBetween(5, 9)}px solid ${pick([
                            'rgba(125, 211, 252, 0.24)',
                            'rgba(251, 191, 36, 0.24)',
                            'rgba(16, 185, 129, 0.22)',
                            'rgba(217, 70, 239, 0.18)',
                        ])}`,
                        background: 'transparent',
                        transformOverride: `rotate(${randomBetween(38, 52)}deg)`,
                    };
                }
            ];

            const accentFactories = [
                () => {
                    const size = accentBetween(18, 42);
                    return {
                        width: size,
                        height: size,
                        borderRadius: '999px',
                        background: pick([
                            'rgba(47, 103, 177, 0.14)',
                            'rgba(245, 155, 47, 0.14)',
                            'rgba(16, 185, 129, 0.12)',
                            'rgba(217, 70, 239, 0.10)',
                        ]),
                    };
                },
                () => {
                    const width = accentBetween(28, 72);
                    const height = accentBetween(8, 20);
                    return {
                        width,
                        height,
                        borderRadius: '999px',
                        background: pick([
                            'rgba(47, 103, 177, 0.16)',
                            'rgba(245, 155, 47, 0.16)',
                            'rgba(16, 185, 129, 0.14)',
                            'rgba(56, 189, 248, 0.14)',
                        ]),
                    };
                },
                () => {
                    const size = accentBetween(24, 58);
                    const stroke = accentBetween(4, 9);
                    return {
                        width: size,
                        height: size,
                        borderRadius: '999px',
                        border: `${stroke}px solid ${pick([
                            'rgba(125, 211, 252, 0.22)',
                            'rgba(251, 191, 36, 0.22)',
                            'rgba(16, 185, 129, 0.18)',
                            'rgba(217, 70, 239, 0.16)',
                        ])}`,
                        background: 'transparent',
                    };
                },
                () => {
                    const size = accentBetween(20, 46);
                    return {
                        width: size,
                        height: size,
                        background: pick([
                            'rgba(245, 155, 47, 0.12)',
                            'rgba(125, 211, 252, 0.12)',
                            'rgba(16, 185, 129, 0.10)',
                        ]),
                        clipPath: 'polygon(50% 0%, 100% 50%, 50% 100%, 0% 50%)',
                    };
                }
            ];

            for (let row = 0; row < rows; row += 1) {
                for (let col = 0; col < columns; col += 1) {
                    const shape = shapeFactories[Math.floor(Math.random() * shapeFactories.length)]();
                    const offsetX = randomBetween(cellWidth * 0.16, cellWidth * 0.84);
                    const offsetY = randomBetween(cellHeight * 0.14, cellHeight * 0.86);
                    const left = clamp((col * cellWidth) + offsetX - (shape.width / 2), 0, width - shape.width);
                    const top = clamp((row * cellHeight) + offsetY - (shape.height / 2), 0, height - shape.height);

                    shapes.push({
                        left,
                        top,
                        rotate: randomBetween(-32, 32),
                        opacity: randomBetween(0.72, 1),
                        ...shape,
                    });
                }
            }

            const accentShapesCount = compact ? 18 : (medium ? 28 : 42);

            for (let index = 0; index < accentShapesCount; index += 1) {
                const shape = accentFactories[Math.floor(Math.random() * accentFactories.length)]();
                const left = clamp(randomBetween(width * 0.03, width * 0.97) - (shape.width / 2), 0, width - shape.width);
                const top = clamp(randomBetween(height * 0.03, height * 0.97) - (shape.height / 2), 0, height - shape.height);

                shapes.push({
                    left,
                    top,
                    rotate: randomBetween(-40, 40),
                    opacity: randomBetween(0.5, 0.88),
                    ...shape,
                });
            }

            layer.replaceChildren();

            for (const shape of shuffle(shapes)) {
                const node = document.createElement('span');
                node.style.position = 'absolute';
                node.style.left = `${shape.left}px`;
                node.style.top = `${shape.top}px`;
                node.style.width = `${shape.width}px`;
                node.style.height = `${shape.height}px`;
                node.style.transform = `rotate(${shape.rotate}deg)`;
                node.style.borderRadius = shape.borderRadius;
                node.style.opacity = shape.opacity;
                node.style.background = shape.background || 'transparent';
                node.style.border = shape.border || 'none';
                node.style.filter = shape.filter || 'none';
                node.style.clipPath = shape.clipPath || 'none';
                if (shape.transformOverride) {
                    node.style.transform = shape.transformOverride;
                }
                layer.appendChild(node);
            }
        }

        function randomizeAppBackgroundIcons() {
            const random = (min, max) => min + Math.random() * (max - min);

            const shuffle = (items) => {
                const copy = [...items];
                for (let index = copy.length - 1; index > 0; index -= 1) {
                    const swapIndex = Math.floor(Math.random() * (index + 1));
                    [copy[index], copy[swapIndex]] = [copy[swapIndex], copy[index]];
                }

                return copy;
            };

            const slots = {
                left: [
                    [6, 12],
                    [23, 30],
                    [38, 45],
                    [54, 61],
                    [69, 76],
                    [83, 88],
                ],
                right: [
                    [4, 10],
                    [15, 21],
                    [28, 35],
                    [42, 49],
                    [56, 63],
                    [70, 77],
                    [84, 89],
                ],
            };

            const horizontal = {
                left: {
                    chat: [3.2, 6.4],
                    aa: [4, 9],
                    comma: [5, 11],
                    dots: [8.5, 13.5],
                    ring: [14, 22],
                },
                right: {
                    quote: [9, 14],
                    dots: [9, 14],
                    ring: [12, 18],
                    document: [3.6, 6.8],
                    braces: [8, 13],
                    'dots-low': [10, 15],
                    check: [5, 8.5],
                },
            };

            const placeIcons = (side) => {
                const icons = [...document.querySelectorAll(`[data-app-bg-side="${side}"]`)]
                    .map((icon) => ({
                        icon,
                        width: icon.offsetWidth || icon.getBoundingClientRect().width || 1,
                        height: icon.offsetHeight || icon.getBoundingClientRect().height || icon.offsetWidth || 1,
                    }))
                    .filter((item) => item.width > 1 && item.height > 1)
                    .sort((first, second) => (second.width * second.height) - (first.width * first.height));

                const viewport = {
                    width: window.innerWidth || document.documentElement.clientWidth || 1,
                    height: window.innerHeight || document.documentElement.clientHeight || 1,
                };
                const margin = 8;
                const occupied = [];

                const rectFromBase = (baseLeft, baseTop, width, height, scale) => {
                    const scaledWidth = width * scale;
                    const scaledHeight = height * scale;

                    return {
                        left: baseLeft - ((scaledWidth - width) / 2),
                        top: baseTop - ((scaledHeight - height) / 2),
                        right: baseLeft - ((scaledWidth - width) / 2) + scaledWidth,
                        bottom: baseTop - ((scaledHeight - height) / 2) + scaledHeight,
                        width: scaledWidth,
                        height: scaledHeight,
                    };
                };

                const clampCandidate = (candidate, width, height, scale) => {
                    let rect = rectFromBase(candidate.left, candidate.top, width, height, scale);

                    if (rect.left < margin) {
                        candidate.left += margin - rect.left;
                    }
                    if (rect.right > viewport.width - margin) {
                        candidate.left -= rect.right - (viewport.width - margin);
                    }
                    if (rect.top < margin) {
                        candidate.top += margin - rect.top;
                    }
                    if (rect.bottom > viewport.height - margin) {
                        candidate.top -= rect.bottom - (viewport.height - margin);
                    }

                    candidate.left = Math.max(margin, Math.min(candidate.left, viewport.width - width - margin));
                    candidate.top = Math.max(margin, Math.min(candidate.top, viewport.height - height - margin));
                    rect = rectFromBase(candidate.left, candidate.top, width, height, scale);

                    return {
                        ...candidate,
                        rect,
                    };
                };

                const overlapRatio = (first, second) => {
                    const width = Math.max(0, Math.min(first.right, second.right) - Math.max(first.left, second.left));
                    const height = Math.max(0, Math.min(first.bottom, second.bottom) - Math.max(first.top, second.top));
                    const area = width * height;

                    if (!area) {
                        return 0;
                    }

                    const smallerArea = Math.max(1, Math.min(first.width * first.height, second.width * second.height));

                    return area / smallerArea;
                };

                const buildCandidate = (item, slot) => {
                    const name = item.icon.dataset.appBgIcon;
                    const xRange = horizontal[side][name] || [4, 10];
                    const scale = random(0.92, 1.08);
                    const top = (random(slot[0], slot[1]) / 100) * viewport.height;
                    const sideOffset = (random(xRange[0], xRange[1]) / 100) * viewport.width;
                    const left = side === 'left'
                        ? sideOffset
                        : viewport.width - sideOffset - item.width;

                    return clampCandidate({ left, top, scale }, item.width, item.height, scale);
                };

                icons.forEach((item, index) => {
                    const sideSlots = shuffle(slots[side]);
                    let best = null;

                    for (let attempt = 0; attempt < 180; attempt += 1) {
                        const slot = sideSlots[(index + attempt) % sideSlots.length];
                        const candidate = buildCandidate(item, slot);
                        const overlaps = occupied.map((rect) => overlapRatio(candidate.rect, rect));
                        const maxOverlap = overlaps.length ? Math.max(...overlaps) : 0;
                        const totalOverlap = overlaps.reduce((sum, value) => sum + value, 0);
                        const score = (maxOverlap * 1000) + (totalOverlap * 100) + random(0, 1);

                        if (!best || score < best.score) {
                            best = {
                                ...candidate,
                                maxOverlap,
                                totalOverlap,
                                score,
                            };
                        }

                        if (maxOverlap <= 0.08 && totalOverlap <= 0.14) {
                            break;
                        }
                    }

                    const chosen = best || buildCandidate(item, slots[side][index % slots[side].length]);
                    item.icon.style.top = `${chosen.top.toFixed(0)}px`;
                    item.icon.style.setProperty('--random-scale', chosen.scale.toFixed(2));

                    if (side === 'left') {
                        item.icon.style.left = `${chosen.left.toFixed(0)}px`;
                        item.icon.style.right = 'auto';
                    } else {
                        item.icon.style.right = `${(viewport.width - chosen.left - item.width).toFixed(0)}px`;
                        item.icon.style.left = 'auto';
                    }

                    const inner = item.icon.querySelector('.app-bg-icon-inner');
                    if (inner) {
                        inner.style.animationDelay = `-${random(0, 6).toFixed(2)}s`;
                    }

                    occupied.push(chosen.rect);
                });
            };

            placeIcons('left');
            placeIcons('right');
        }

        document.addEventListener('DOMContentLoaded', () => {
            initStickyShellHeader();
            randomizeAppBackgroundIcons();
            window.requestAnimationFrame(buildShellRandomShapes);

            let shellShapesResizeTimeout = null;
            const scheduleShellShapes = () => {
                window.clearTimeout(shellShapesResizeTimeout);
                shellShapesResizeTimeout = window.setTimeout(buildShellRandomShapes, 180);
            };

            let appBackgroundResizeTimeout = null;
            const scheduleAppBackgroundIcons = () => {
                window.clearTimeout(appBackgroundResizeTimeout);
                appBackgroundResizeTimeout = window.setTimeout(randomizeAppBackgroundIcons, 180);
            };

            window.addEventListener('load', scheduleShellShapes, { once: true });
            window.addEventListener('resize', scheduleShellShapes);
            window.addEventListener('resize', scheduleAppBackgroundIcons);
        });
    </script>
    @livewireScripts
    @yield('scripts')
</body>
</html>
