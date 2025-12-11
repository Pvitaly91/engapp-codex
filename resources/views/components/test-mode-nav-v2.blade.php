<nav class="mb-8">
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 p-4 sm:p-5">
            <div class="flex flex-wrap items-center gap-3">
                <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">View mode</span>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('test.show', $test->slug) }}"
                       class="inline-flex items-center rounded-xl border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 transition hover:-translate-y-0.5 hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700 {{ !\Illuminate\Support\Str::contains(request()->path(), 'step') ? 'bg-indigo-600 text-white border-indigo-500 shadow-sm' : '' }}">
                        Card view
                    </a>
                    <a href="{{ route('test.step', $test->slug) }}"
                       class="inline-flex items-center rounded-xl border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 transition hover:-translate-y-0.5 hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700 {{ \Illuminate\Support\Str::contains(request()->path(), 'step') ? 'bg-indigo-600 text-white border-indigo-500 shadow-sm' : '' }}">
                        Step view
                    </a>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Difficulty</span>
                <div class="flex flex-wrap gap-2">
                    @if (\Illuminate\Support\Str::contains(request()->path(), 'step'))
                        <a href="{{ route('test.step', $test->slug) }}" class="inline-flex items-center rounded-xl border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 transition hover:-translate-y-0.5 hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700 {{ request()->routeIs('test.step') ? 'bg-indigo-600 text-white border-indigo-500 shadow-sm' : '' }}">Easy</a>
                        <a href="{{ route('test.step-select', $test->slug) }}" class="inline-flex items-center rounded-xl border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 transition hover:-translate-y-0.5 hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700 {{ request()->routeIs('test.step-select') ? 'bg-indigo-600 text-white border-indigo-500 shadow-sm' : '' }}">Medium</a>
                        <a href="{{ route('test.step-input', $test->slug) }}" class="inline-flex items-center rounded-xl border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 transition hover:-translate-y-0.5 hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700 {{ request()->routeIs('test.step-input') ? 'bg-indigo-600 text-white border-indigo-500 shadow-sm' : '' }}">Hard</a>
                        <a href="{{ route('test.step-manual', $test->slug) }}" class="inline-flex items-center rounded-xl border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 transition hover:-translate-y-0.5 hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700 {{ request()->routeIs('test.step-manual') ? 'bg-indigo-600 text-white border-indigo-500 shadow-sm' : '' }}">Expert</a>
                    @else
                        <a href="{{ route('test.show', $test->slug) }}" class="inline-flex items-center rounded-xl border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 transition hover:-translate-y-0.5 hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700 {{ request()->routeIs('test.show') ? 'bg-indigo-600 text-white border-indigo-500 shadow-sm' : '' }}">Easy</a>
                        <a href="{{ route('test.select', $test->slug) }}" class="inline-flex items-center rounded-xl border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 transition hover:-translate-y-0.5 hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700 {{ request()->routeIs('test.select') ? 'bg-indigo-600 text-white border-indigo-500 shadow-sm' : '' }}">Medium</a>
                        <a href="{{ route('test.input', $test->slug) }}" class="inline-flex items-center rounded-xl border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 transition hover:-translate-y-0.5 hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700 {{ request()->routeIs('test.input') ? 'bg-indigo-600 text-white border-indigo-500 shadow-sm' : '' }}">Hard</a>
                        <a href="{{ route('test.manual', $test->slug) }}" class="inline-flex items-center rounded-xl border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 transition hover:-translate-y-0.5 hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700 {{ request()->routeIs('test.manual') ? 'bg-indigo-600 text-white border-indigo-500 shadow-sm' : '' }}">Expert</a>
                    @endif
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Games</span>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('saved-test.js.drag-drop', $test->slug) }}" class="inline-flex items-center rounded-xl border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 transition hover:-translate-y-0.5 hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700 {{ request()->routeIs('saved-test.js.drag-drop') ? 'bg-indigo-600 text-white border-indigo-500 shadow-sm' : '' }}">Drag &amp; Drop</a>
                    <a href="{{ route('saved-test.js.match', $test->slug) }}" class="inline-flex items-center rounded-xl border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 transition hover:-translate-y-0.5 hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700 {{ request()->routeIs('saved-test.js.match') ? 'bg-indigo-600 text-white border-indigo-500 shadow-sm' : '' }}">Match</a>
                    <a href="{{ route('saved-test.js.dialogue', $test->slug) }}" class="inline-flex items-center rounded-xl border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 transition hover:-translate-y-0.5 hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700 {{ request()->routeIs('saved-test.js.dialogue') ? 'bg-indigo-600 text-white border-indigo-500 shadow-sm' : '' }}">Dialogue</a>
                </div>
            </div>
        </div>
    </div>
</nav>
