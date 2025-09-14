<nav class="mb-6 text-sm space-y-2">
    <div class="flex items-center gap-2">
        <span class="font-semibold">Карти:</span>
        <a href="{{ route('saved-test.js', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ request()->routeIs('saved-test.js') ? 'bg-stone-900 text-white' : '' }}">Карти</a>
        <a href="{{ route('saved-test.js.manual', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ request()->routeIs('saved-test.js.manual') ? 'bg-stone-900 text-white' : '' }}">Manual</a>
        <a href="{{ route('saved-test.js.input', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ request()->routeIs('saved-test.js.input') ? 'bg-stone-900 text-white' : '' }}">Input All</a>
        <a href="{{ route('saved-test.js.select', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ request()->routeIs('saved-test.js.select') ? 'bg-stone-900 text-white' : '' }}">Select All</a>
    </div>
    <div class="flex items-center gap-2">
        <span class="font-semibold">Step:</span>
        <a href="{{ route('saved-test.js.step', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ request()->routeIs('saved-test.js.step') ? 'bg-stone-900 text-white' : '' }}">Step</a>
        <a href="{{ route('saved-test.js.step-manual', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ request()->routeIs('saved-test.js.step-manual') ? 'bg-stone-900 text-white' : '' }}">Step Manual</a>
        <a href="{{ route('saved-test.js.step-input', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ request()->routeIs('saved-test.js.step-input') ? 'bg-stone-900 text-white' : '' }}">Step Input</a>
        <a href="{{ route('saved-test.js.step-select', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ request()->routeIs('saved-test.js.step-select') ? 'bg-stone-900 text-white' : '' }}">Step Select</a>
    </div>
</nav>
