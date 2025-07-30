<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'English App')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">

    <!-- Навігація -->
    <nav class="bg-white shadow py-4 mb-6">
        <div class="container mx-auto flex justify-between items-center px-4">
            <a href="{{ url('/') }}" class="text-2xl font-bold text-blue-700">English Test Hub</a>

            <!-- Пошук по словах -->
            <div x-data="predictiveSearch()" class="relative w-72 mx-4">
                <input
                    id="word_search"
                    type="text"
                    x-model="query"
                    @input="search"
                    class="w-full rounded-xl border px-4 py-2 focus:outline-blue-400"
                    placeholder="{{ __('messages.search_placeholder') }}"
                    autocomplete="off"
                >
                <!-- Список підказок, absolute! -->
                <ul 
                    x-show="results.length" 
                    class="absolute left-0 right-0 bg-white shadow rounded-xl divide-y mt-1 z-50"
                    style="top: 100%;"
                >
                    <template x-for="item in results" :key="item.en">
                        <li class="px-4 py-2 hover:bg-blue-50 cursor-pointer flex items-center justify-between">
                            <span class="font-medium text-gray-900" x-text="item.en"></span>
                            <span class="ml-3 text-gray-500" x-text="item.translation"></span>
                        </li>
                    </template>
                </ul>
            </div>

            <ul class="flex gap-6 text-gray-600 font-medium">
                <li><a href="{{ url('/words/test') }}" class="hover:text-blue-500">Words</a></li>
                <li><a href="{{ route('translate.test') }}" class="hover:text-blue-500">Translate Test</a></li>
                <li><a href="{{ route('question-review.index') }}" class="hover:text-blue-500">Question Review</a></li>
                <li><a href="{{ route('saved-tests.cards') }}" class="hover:text-blue-500">Tests Catalog</a></li>
                <li><a href="{{ url('/about') }}" class="hover:text-blue-500">About</a></li>
            </ul>
            <!-- Dropdown у layout'і -->
            <form method="GET" action="{{ route('setlocale') }}" class="inline-block ml-4 align-middle">
                <select name="lang" onchange="this.form.submit()" class="w-20 min-w-[60px] max-w-[80px] text-center border rounded px-2 py-1 text-sm bg-white shadow focus:outline-blue-400">
                    <option value="en" {{ app()->getLocale() == 'en' ? 'selected' : '' }}>EN</option>
                    <option value="uk" {{ app()->getLocale() == 'uk' ? 'selected' : '' }}>UA</option>
                    <option value="pl" {{ app()->getLocale() == 'pl' ? 'selected' : '' }}>PL</option>
                </select>
            </form>
        </div>
    </nav>

    <!-- Alpine.js для реактивності -->
    <script src="//unpkg.com/alpinejs" defer></script>
    <script>
    function predictiveSearch() {
        return {
            query: '',
            results: [],
            lang: 'uk', // автоматично підставляє обрану мову!
            search() {
                if (this.query.length < 2) {
                    this.results = [];
                    return;
                }
                fetch('/api/search?q=' + encodeURIComponent(this.query) + '&lang=' + this.lang)
                    .then(res => res.json())
                    .then(data => this.results = data);
            }
        }
    }
    </script>
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
