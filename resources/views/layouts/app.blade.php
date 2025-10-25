<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'English App')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .text-base{
            line-height: 2.2rem !important;
        }
        .test-description {
            background-color: #fefce8; /* yellow-50 */
            border-left: 4px solid #f59e0b; /* yellow-500 */
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">

    <!-- Навігація -->
    <nav class="bg-white shadow mb-6" x-data="{ open: false }">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between py-4">
                <a href="{{ route('admin.dashboard') }}" class="text-2xl font-bold text-blue-700">
                    Admin Hub
                </a>
                <button
                    type="button"
                    class="md:hidden text-gray-600 hover:text-blue-600 focus:outline-none"
                    @click="open = !open"
                    aria-label="Toggle navigation"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                @php($supportsShell = function_exists('proc_open'))

                <div class="hidden md:flex md:items-center md:gap-6 text-gray-600 font-medium">
                    <a href="{{ route('pages.manage.index') }}" class="hover:text-blue-500 transition">Сторінки</a>
                    <a href="{{ route('grammar-test') }}" class="hover:text-blue-500 transition">Граматика</a>
                    <a href="{{ route('saved-tests.list') }}" class="hover:text-blue-500 transition">Збережені тести</a>
                    <a href="{{ route('seed-runs.index') }}" class="hover:text-blue-500 transition">Seed Runs</a>
                    <div
                        x-data="{ open: false }"
                        class="relative"
                        @mouseenter="open = true"
                        @mouseleave="open = false"
                        @focusin="open = true"
                        @focusout="open = false"
                    >
                        <button
                            type="button"
                            class="inline-flex items-center gap-1 hover:text-blue-500 transition"
                        >
                            {{ $supportsShell ? 'Деплой' : 'Git' }}
                            <i class="fa-solid fa-chevron-down text-xs transition-transform" :class="{ 'rotate-180': open }"></i>
                        </button>
                        <div
                            x-show="open"
                            x-transition
                            x-cloak
                            @mouseenter="open = true"
                            @mouseleave="open = false"
                            class="absolute right-0 mt-2 w-48 rounded-lg border border-gray-100 bg-white py-2 text-sm shadow-lg"
                        >
                            @if($supportsShell)
                                <a href="{{ route('deployment.index') }}" class="block px-4 py-2 hover:bg-blue-50">Shell версія</a>
                                <a href="{{ route('deployment.native.index') }}" class="block px-4 py-2 hover:bg-blue-50">Без shell</a>
                            @else
                                <a href="{{ route('deployment.native.index') }}" class="block px-4 py-2 hover:bg-blue-50">Git</a>
                            @endif
                            <a href="{{ route('migrations.index') }}" class="block px-4 py-2 hover:bg-blue-50">Міграції</a>
                        </div>
                    </div>
                    <a href="{{ route('home') }}" class="hover:text-blue-500 transition">До публічної частини</a>
                    @if(session('admin_authenticated'))
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-red-500 hover:text-red-600 transition">Вийти</button>
                        </form>
                    @else
                        <a href="{{ route('login.show') }}" class="hover:text-blue-500 transition">Увійти</a>
                    @endif
                </div>
            </div>
            <div
                class="md:hidden border-t border-gray-100 pt-2 pb-4 space-y-2 text-gray-600 font-medium"
                x-show="open"
                x-cloak
                x-transition
            >
                <a href="{{ route('pages.manage.index') }}" class="block px-2 py-2 rounded-lg hover:bg-blue-50">Сторінки</a>
                <a href="{{ route('grammar-test') }}" class="block px-2 py-2 rounded-lg hover:bg-blue-50">Граматика</a>
                <a href="{{ route('saved-tests.list') }}" class="block px-2 py-2 rounded-lg hover:bg-blue-50">Збережені тести</a>
                <a href="{{ route('seed-runs.index') }}" class="block px-2 py-2 rounded-lg hover:bg-blue-50">Seed Runs</a>
                <div x-data="{ openDeployment: false }" class="space-y-1">
                    <button
                        type="button"
                        class="flex w-full items-center justify-between rounded-lg px-2 py-2 hover:bg-blue-50"
                        @click="openDeployment = !openDeployment"
                    >
                        <span>{{ $supportsShell ? 'Деплой' : 'Git' }}</span>
                        <i class="fa-solid fa-chevron-down text-xs transition-transform" :class="{ 'rotate-180': openDeployment }"></i>
                    </button>
                    <div x-show="openDeployment" x-transition x-cloak class="ml-4 space-y-1 text-sm">
                        @if($supportsShell)
                            <a href="{{ route('deployment.index') }}" class="block rounded-lg px-2 py-1.5 hover:bg-blue-50">Shell версія</a>
                            <a href="{{ route('deployment.native.index') }}" class="block rounded-lg px-2 py-1.5 hover:bg-blue-50">Без shell</a>
                        @else
                            <a href="{{ route('deployment.native.index') }}" class="block rounded-lg px-2 py-1.5 hover:bg-blue-50">Git</a>
                        @endif
                        <a href="{{ route('migrations.index') }}" class="block rounded-lg px-2 py-1.5 hover:bg-blue-50">Міграції</a>
                    </div>
                </div>
                <a href="{{ route('home') }}" class="block px-2 py-2 rounded-lg hover:bg-blue-50">До публічної частини</a>
                @if(session('admin_authenticated'))
                    <form method="POST" action="{{ route('logout') }}" class="px-2 py-2">
                        @csrf
                        <button type="submit" class="w-full text-left text-red-500 hover:text-red-600">Вийти</button>
                    </form>
                @else
                    <a href="{{ route('login.show') }}" class="block px-2 py-2 rounded-lg hover:bg-blue-50">Увійти</a>
                @endif
            </div>
        </div>
    </nav>

    <!-- Alpine.js для реактивності -->
    <script src="//unpkg.com/alpinejs" defer></script>
    <!-- Контент -->
    <main class="flex-1 container mx-auto px-4">
        @php $pageTitle = trim($__env->yieldContent('title')); @endphp
        @if(isset($breadcrumbs))
            @include('components.breadcrumbs', ['items' => $breadcrumbs])
        @elseif(!request()->is('admin') && !empty($pageTitle))
            @include('components.breadcrumbs', ['items' => [
                ['label' => 'Home', 'url' => route('admin.dashboard')],
                ['label' => $pageTitle]
            ]])
        @endif
        @yield('content')
    </main>

    <!-- Підвал -->
    <footer class="bg-white border-t mt-8 py-4 text-center text-sm text-gray-400">
        &copy; {{ date('Y') }} English Test Hub. All rights reserved.
    </footer>
</body>
</html>
