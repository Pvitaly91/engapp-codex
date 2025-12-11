<!-- Test Controls Navigation - Modern Redesign -->
<nav class="mb-8 bg-white rounded-2xl shadow-md border border-gray-100 p-4 sm:p-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 sm:gap-6">
        <!-- View Mode -->
        <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3">
            <span class="text-sm font-semibold text-gray-700 whitespace-nowrap">
                <svg class="w-4 h-4 inline-block mr-1 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                </svg>
                View Mode
            </span>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('test.show', $test->slug) }}" class="px-4 py-2 text-sm font-medium rounded-xl border-2 transition-all duration-200 {{ !\Illuminate\Support\Str::contains(request()->path(), 'step') ? 'bg-gradient-to-r from-indigo-500 to-purple-500 text-white border-transparent shadow-md' : 'border-gray-200 text-gray-600 hover:border-indigo-300 hover:bg-indigo-50' }}">
                    Card
                </a>
                <a href="{{ route('test.step', $test->slug) }}" class="px-4 py-2 text-sm font-medium rounded-xl border-2 transition-all duration-200 {{ \Illuminate\Support\Str::contains(request()->path(), 'step') ? 'bg-gradient-to-r from-indigo-500 to-purple-500 text-white border-transparent shadow-md' : 'border-gray-200 text-gray-600 hover:border-indigo-300 hover:bg-indigo-50' }}">
                    Step
                </a>
            </div>
        </div>

        <!-- Difficulty -->
        <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3">
            <span class="text-sm font-semibold text-gray-700 whitespace-nowrap">
                <svg class="w-4 h-4 inline-block mr-1 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                Difficulty
            </span>
            <div class="flex flex-wrap gap-2">
                @if (\Illuminate\Support\Str::contains(request()->path(), 'step'))
                    <a href="{{ route('test.step', $test->slug) }}" class="px-4 py-2 text-sm font-medium rounded-xl border-2 transition-all duration-200 {{ request()->routeIs('test.step') ? 'bg-gradient-to-r from-emerald-500 to-teal-500 text-white border-transparent shadow-md' : 'border-gray-200 text-gray-600 hover:border-emerald-300 hover:bg-emerald-50' }}">Easy</a>
                    <a href="{{ route('test.step-select', $test->slug) }}" class="px-4 py-2 text-sm font-medium rounded-xl border-2 transition-all duration-200 {{ request()->routeIs('test.step-select') ? 'bg-gradient-to-r from-amber-500 to-orange-500 text-white border-transparent shadow-md' : 'border-gray-200 text-gray-600 hover:border-amber-300 hover:bg-amber-50' }}">Medium</a>
                    <a href="{{ route('test.step-input', $test->slug) }}" class="px-4 py-2 text-sm font-medium rounded-xl border-2 transition-all duration-200 {{ request()->routeIs('test.step-input') ? 'bg-gradient-to-r from-rose-500 to-pink-500 text-white border-transparent shadow-md' : 'border-gray-200 text-gray-600 hover:border-rose-300 hover:bg-rose-50' }}">Hard</a>
                    <a href="{{ route('test.step-manual', $test->slug) }}" class="px-4 py-2 text-sm font-medium rounded-xl border-2 transition-all duration-200 {{ request()->routeIs('test.step-manual') ? 'bg-gradient-to-r from-purple-500 to-violet-500 text-white border-transparent shadow-md' : 'border-gray-200 text-gray-600 hover:border-purple-300 hover:bg-purple-50' }}">Expert</a>
                @else
                    <a href="{{ route('test.show', $test->slug) }}" class="px-4 py-2 text-sm font-medium rounded-xl border-2 transition-all duration-200 {{ request()->routeIs('test.show') ? 'bg-gradient-to-r from-emerald-500 to-teal-500 text-white border-transparent shadow-md' : 'border-gray-200 text-gray-600 hover:border-emerald-300 hover:bg-emerald-50' }}">Easy</a>
                    <a href="{{ route('test.select', $test->slug) }}" class="px-4 py-2 text-sm font-medium rounded-xl border-2 transition-all duration-200 {{ request()->routeIs('test.select') ? 'bg-gradient-to-r from-amber-500 to-orange-500 text-white border-transparent shadow-md' : 'border-gray-200 text-gray-600 hover:border-amber-300 hover:bg-amber-50' }}">Medium</a>
                    <a href="{{ route('test.input', $test->slug) }}" class="px-4 py-2 text-sm font-medium rounded-xl border-2 transition-all duration-200 {{ request()->routeIs('test.input') ? 'bg-gradient-to-r from-rose-500 to-pink-500 text-white border-transparent shadow-md' : 'border-gray-200 text-gray-600 hover:border-rose-300 hover:bg-rose-50' }}">Hard</a>
                    <a href="{{ route('test.manual', $test->slug) }}" class="px-4 py-2 text-sm font-medium rounded-xl border-2 transition-all duration-200 {{ request()->routeIs('test.manual') ? 'bg-gradient-to-r from-purple-500 to-violet-500 text-white border-transparent shadow-md' : 'border-gray-200 text-gray-600 hover:border-purple-300 hover:bg-purple-50' }}">Expert</a>
                @endif
            </div>
        </div>

        <!-- Games -->
        <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3">
            <span class="text-sm font-semibold text-gray-700 whitespace-nowrap">
                <svg class="w-4 h-4 inline-block mr-1 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Games
            </span>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('saved-test.js.drag-drop', $test->slug) }}" class="px-4 py-2 text-sm font-medium rounded-xl border-2 transition-all duration-200 {{ request()->routeIs('saved-test.js.drag-drop') ? 'bg-gradient-to-r from-cyan-500 to-blue-500 text-white border-transparent shadow-md' : 'border-gray-200 text-gray-600 hover:border-cyan-300 hover:bg-cyan-50' }}">Drag & Drop</a>
                <a href="{{ route('saved-test.js.match', $test->slug) }}" class="px-4 py-2 text-sm font-medium rounded-xl border-2 transition-all duration-200 {{ request()->routeIs('saved-test.js.match') ? 'bg-gradient-to-r from-cyan-500 to-blue-500 text-white border-transparent shadow-md' : 'border-gray-200 text-gray-600 hover:border-cyan-300 hover:bg-cyan-50' }}">Match</a>
                <a href="{{ route('saved-test.js.dialogue', $test->slug) }}" class="px-4 py-2 text-sm font-medium rounded-xl border-2 transition-all duration-200 {{ request()->routeIs('saved-test.js.dialogue') ? 'bg-gradient-to-r from-cyan-500 to-blue-500 text-white border-transparent shadow-md' : 'border-gray-200 text-gray-600 hover:border-cyan-300 hover:bg-cyan-50' }}">Dialogue</a>
            </div>
        </div>
    </div>
</nav>
