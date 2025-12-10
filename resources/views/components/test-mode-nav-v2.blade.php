<nav class="mb-6 text-sm space-y-2">
    <div class="flex items-center gap-2">
        <span class="font-semibold">View mode:</span>
        <a href="{{ route('saved-test.js-v2', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ !\Illuminate\Support\Str::contains(request()->path(), 'step') ? 'bg-stone-900 text-white' : '' }}">card</a>
        <a href="{{ route('saved-test.js-v2.step', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ \Illuminate\Support\Str::contains(request()->path(), 'step') ? 'bg-stone-900 text-white' : '' }}">step</a>
    </div> 
    <div class="flex items-center gap-2">
         <span class="font-semibold">Diffculity:</span>
        @if (\Illuminate\Support\Str::contains(request()->path(), 'step'))
            <a href="{{ route('saved-test.js-v2.step', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ request()->routeIs('saved-test.js-v2.step') ? 'bg-stone-900 text-white' : '' }}">Easy</a>
            <a href="{{ route('saved-test.js-v2.step-select', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ request()->routeIs('saved-test.js-v2.step-select') ? 'bg-stone-900 text-white' : '' }}">Mdium</a>
            <a href="{{ route('saved-test.js-v2.step-input', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ request()->routeIs('saved-test.js-v2.step-input') ? 'bg-stone-900 text-white' : '' }}">Hard</a>
            <a href="{{ route('saved-test.js-v2.step-manual', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ request()->routeIs('saved-test.js-v2.step-manual') ? 'bg-stone-900 text-white' : '' }}">Expert</a>
        @else
            <a href="{{ route('saved-test.js-v2', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ request()->routeIs('saved-test.js-v2') ? 'bg-stone-900 text-white' : '' }}">Easy</a>
            <a href="{{ route('saved-test.js-v2.select', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ request()->routeIs('saved-test.js-v2.select') ? 'bg-stone-900 text-white' : '' }}">Mdium</a>
            <a href="{{ route('saved-test.js-v2.input', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ request()->routeIs('saved-test.js-v2.input') ? 'bg-stone-900 text-white' : '' }}">Hard</a>
            <a href="{{ route('saved-test.js-v2.manual', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ request()->routeIs('saved-test.js-v2.manual') ? 'bg-stone-900 text-white' : '' }}">Expert</a>
        @endif
     </div>
    <div class="flex items-center gap-2">
        <span class="font-semibold">Games:</span>
        <a href="{{ route('saved-test.js.drag-drop', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ request()->routeIs('saved-test.js.drag-drop') ? 'bg-stone-900 text-white' : '' }}">Drag &amp; Drop</a>
        <a href="{{ route('saved-test.js.match', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ request()->routeIs('saved-test.js.match') ? 'bg-stone-900 text-white' : '' }}">Match</a>
        <a href="{{ route('saved-test.js.dialogue', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ request()->routeIs('saved-test.js.dialogue') ? 'bg-stone-900 text-white' : '' }}">Dialogue</a>
    </div>
</nav>
