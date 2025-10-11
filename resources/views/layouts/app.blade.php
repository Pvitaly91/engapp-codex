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
                <a href="{{ url('/tests') }}" class="text-2xl font-bold text-blue-700">
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
                <div class="hidden md:flex md:items-center md:gap-6 text-gray-600 font-medium">
                    <a href="{{ route('pages-v2.manage.index') }}" class="hover:text-blue-500 transition">Сторінки v2</a>
                    <a href="{{ url('/grammar-test/v2') }}" class="hover:text-blue-500 transition">Граматика v2</a>
                    <a href="{{ url('/tests') }}" class="hover:text-blue-500 transition">Збережені тести</a>
                    <a href="{{ route('seed-runs.index') }}" class="hover:text-blue-500 transition">Seed Runs</a>
                    <a href="{{ url('/') }}" class="hover:text-blue-500 transition">До публічної частини</a>
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
                <a href="{{ route('pages-v2.manage.index') }}" class="block px-2 py-2 rounded-lg hover:bg-blue-50">Сторінки v2</a>
                <a href="{{ url('/grammar-test/v2') }}" class="block px-2 py-2 rounded-lg hover:bg-blue-50">Граматика v2</a>
                <a href="{{ url('/tests') }}" class="block px-2 py-2 rounded-lg hover:bg-blue-50">Збережені тести</a>
                <a href="{{ route('seed-runs.index') }}" class="block px-2 py-2 rounded-lg hover:bg-blue-50">Seed Runs</a>
                <a href="{{ url('/') }}" class="block px-2 py-2 rounded-lg hover:bg-blue-50">До публічної частини</a>
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
        @elseif(request()->path() !== '/' && !empty($pageTitle))
            @include('components.breadcrumbs', ['items' => [
                ['label' => 'Home', 'url' => route('home')],
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
