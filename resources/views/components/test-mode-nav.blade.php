<nav class="mb-6 text-sm space-y-2">
    <div class="flex items-center gap-2">
        <span class="font-semibold">View mode:</span>
        <a href="{{ localized_route('test.show', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ !\Illuminate\Support\Str::contains(request()->path(), 'step') ? 'bg-stone-900 text-white' : '' }}">card</a>
        <a href="{{ localized_route('test.step', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ \Illuminate\Support\Str::contains(request()->path(), 'step') ? 'bg-stone-900 text-white' : '' }}">step</a>
    </div> 
    <div class="flex items-center gap-2">
         <span class="font-semibold">Diffculity:</span>
        @if (\Illuminate\Support\Str::contains(request()->path(), 'step'))
            <a href="{{ localized_route('test.step', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ request()->routeIs('test.step') ? 'bg-stone-900 text-white' : '' }}">Easy</a>
            <a href="{{ localized_route('test.step-select', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ request()->routeIs('test.step-select') ? 'bg-stone-900 text-white' : '' }}">Mdium</a>
            <a href="{{ localized_route('test.step-input', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ request()->routeIs('test.step-input') ? 'bg-stone-900 text-white' : '' }}">Hard</a>
            <a href="{{ localized_route('test.step-manual', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ request()->routeIs('test.step-manual') ? 'bg-stone-900 text-white' : '' }}">Expert</a>
        @else
            <a href="{{ localized_route('test.show', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ request()->routeIs('test.show') ? 'bg-stone-900 text-white' : '' }}">Easy</a>
            <a href="{{ localized_route('test.select', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ request()->routeIs('test.select') ? 'bg-stone-900 text-white' : '' }}">Mdium</a>
            <a href="{{ localized_route('test.input', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ request()->routeIs('test.input') ? 'bg-stone-900 text-white' : '' }}">Hard</a>
            <a href="{{ localized_route('test.manual', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ request()->routeIs('test.manual') ? 'bg-stone-900 text-white' : '' }}">Expert</a>
        @endif
     </div>
    <div class="flex items-center gap-2">
        <span class="font-semibold">Games:</span>
        <a href="{{ localized_route('saved-test.js.drag-drop', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ request()->routeIs('saved-test.js.drag-drop') ? 'bg-stone-900 text-white' : '' }}">Drag &amp; Drop</a>
        <a href="{{ localized_route('saved-test.js.match', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ request()->routeIs('saved-test.js.match') ? 'bg-stone-900 text-white' : '' }}">Match</a>
        <a href="{{ localized_route('saved-test.js.dialogue', $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ request()->routeIs('saved-test.js.dialogue') ? 'bg-stone-900 text-white' : '' }}">Dialogue</a>
    </div>
</nav>
