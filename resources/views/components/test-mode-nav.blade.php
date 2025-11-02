@php
    $routeName = request()->route()?->getName();
    $cardRoutes = [
        'saved-test.js',
        'saved-test.js.select',
        'saved-test.js.input',
        'saved-test.js.manual',
        'saved-test.js.drag-drop',
        'saved-test.js.match',
    ];
    $stepRoutes = [
        'saved-test.js.step',
        'saved-test.js.step-select',
        'saved-test.js.step-input',
        'saved-test.js.step-manual',
    ];
@endphp
<nav class="mb-6 text-sm space-y-2">
    <div class="flex items-center gap-2">
        <span class="font-semibold">View mode:</span>
        <a href="{{ route('saved-test.js', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ in_array($routeName, $cardRoutes, true) ? 'bg-stone-900 text-white' : '' }}">card</a>
        <a href="{{ route('saved-test.js.step', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ in_array($routeName, $stepRoutes, true) ? 'bg-stone-900 text-white' : '' }}">step</a>
    </div>
    <div class="flex items-center gap-2">
         <span class="font-semibold">Diffculity:</span>
        @if (in_array($routeName, $stepRoutes, true))
            <a href="{{ route('saved-test.js.step', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ request()->routeIs('saved-test.js.step') ? 'bg-stone-900 text-white' : '' }}">Easy</a>
            <a href="{{ route('saved-test.js.step-select', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ request()->routeIs('saved-test.js.step-select') ? 'bg-stone-900 text-white' : '' }}">Mdium</a>
            <a href="{{ route('saved-test.js.step-input', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ request()->routeIs('saved-test.js.step-input') ? 'bg-stone-900 text-white' : '' }}">Hard</a>
            <a href="{{ route('saved-test.js.step-manual', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ request()->routeIs('saved-test.js.step-manual') ? 'bg-stone-900 text-white' : '' }}">Expert</a>
        @else
            <a href="{{ route('saved-test.js', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ request()->routeIs('saved-test.js') ? 'bg-stone-900 text-white' : '' }}">Easy</a>
            <a href="{{ route('saved-test.js.select', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ request()->routeIs('saved-test.js.select') ? 'bg-stone-900 text-white' : '' }}">Mdium</a>
            <a href="{{ route('saved-test.js.input', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ request()->routeIs('saved-test.js.input') ? 'bg-stone-900 text-white' : '' }}">Hard</a>
            <a href="{{ route('saved-test.js.manual', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ request()->routeIs('saved-test.js.manual') ? 'bg-stone-900 text-white' : '' }}">Expert</a>
            <a href="{{ route('saved-test.js.match', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ request()->routeIs('saved-test.js.match') ? 'bg-stone-900 text-white' : '' }}">Match</a>
        @endif
     </div>
</nav>
