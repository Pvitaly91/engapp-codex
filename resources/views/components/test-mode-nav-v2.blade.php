<nav class="mb-8">
    <div class="rounded-3xl border border-slate-200/80 bg-white/90 shadow-[0_10px_40px_rgba(15,23,42,0.08)] backdrop-blur">
        <div class="flex flex-col gap-4 p-4 md:flex-row md:items-center md:justify-between md:gap-6 lg:p-6">
            <div class="flex flex-1 flex-col gap-2 md:flex-row md:items-center md:gap-3">
                <span class="flex items-center text-sm font-semibold text-slate-600">
                    <svg class="h-4 w-4 text-indigo-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M3 5.5A1.5 1.5 0 0 1 4.5 4h11A1.5 1.5 0 0 1 17 5.5v0A1.5 1.5 0 0 1 15.5 7h-11A1.5 1.5 0 0 1 3 5.5v0Zm0 4A1.5 1.5 0 0 1 4.5 8h11A1.5 1.5 0 0 1 17 9.5v0A1.5 1.5 0 0 1 15.5 11h-11A1.5 1.5 0 0 1 3 9.5v0Zm0 4A1.5 1.5 0 0 1 4.5 12h11a1.5 1.5 0 0 1 0 3h-11A1.5 1.5 0 0 1 3 13.5v0Z" />
                    </svg>
                    <span class="ml-2">View Mode</span>
                </span>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('test.show', $test->slug) }}"
                       class="inline-flex items-center rounded-full border px-4 py-2 text-sm font-semibold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500 {{ !\Illuminate\Support\Str::contains(request()->path(), 'step') ? 'border-indigo-200 bg-indigo-500 text-white shadow-sm hover:bg-indigo-600 hover:border-indigo-300' : 'border-slate-200 bg-white text-slate-700 hover:-translate-y-0.5 hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700' }}">
                        Card
                    </a>
                    <a href="{{ route('test.step', $test->slug) }}"
                       class="inline-flex items-center rounded-full border px-4 py-2 text-sm font-semibold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500 {{ \Illuminate\Support\Str::contains(request()->path(), 'step') ? 'border-indigo-200 bg-indigo-500 text-white shadow-sm hover:bg-indigo-600 hover:border-indigo-300' : 'border-slate-200 bg-white text-slate-700 hover:-translate-y-0.5 hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700' }}">
                        Step
                    </a>
                </div>
            </div>

            <div class="flex flex-1 flex-col gap-2 md:flex-row md:items-center md:gap-3">
                <span class="flex items-center text-sm font-semibold text-slate-600">
                    <svg class="h-4 w-4 text-amber-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M11 1.5a1 1 0 0 0-1.8-.6l-7 10a1 1 0 0 0 1 .6H9v6.4a1 1 0 0 0 1.8.6l7-10A1 1 0 0 0 17 7.5h-5V1.5Z" />
                    </svg>
                    <span class="ml-2">Difficulty</span>
                </span>
                <div class="flex flex-wrap gap-2">
                    @if (\Illuminate\Support\Str::contains(request()->path(), 'step'))
                        <a href="{{ route('test.step', $test->slug) }}" class="inline-flex items-center rounded-full border px-4 py-2 text-sm font-semibold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-500 {{ request()->routeIs('test.step') ? 'border-emerald-200 bg-emerald-500 text-white shadow-sm hover:bg-emerald-600 hover:border-emerald-300' : 'border-slate-200 bg-white text-slate-700 hover:-translate-y-0.5 hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700' }}">Easy</a>
                        <a href="{{ route('test.step-select', $test->slug) }}" class="inline-flex items-center rounded-full border px-4 py-2 text-sm font-semibold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-500 {{ request()->routeIs('test.step-select') ? 'border-emerald-200 bg-emerald-500 text-white shadow-sm hover:bg-emerald-600 hover:border-emerald-300' : 'border-slate-200 bg-white text-slate-700 hover:-translate-y-0.5 hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700' }}">Medium</a>
                        <a href="{{ route('test.step-input', $test->slug) }}" class="inline-flex items-center rounded-full border px-4 py-2 text-sm font-semibold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-500 {{ request()->routeIs('test.step-input') ? 'border-emerald-200 bg-emerald-500 text-white shadow-sm hover:bg-emerald-600 hover:border-emerald-300' : 'border-slate-200 bg-white text-slate-700 hover:-translate-y-0.5 hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700' }}">Hard</a>
                        <a href="{{ route('test.step-manual', $test->slug) }}" class="inline-flex items-center rounded-full border px-4 py-2 text-sm font-semibold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-500 {{ request()->routeIs('test.step-manual') ? 'border-emerald-200 bg-emerald-500 text-white shadow-sm hover:bg-emerald-600 hover:border-emerald-300' : 'border-slate-200 bg-white text-slate-700 hover:-translate-y-0.5 hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700' }}">Expert</a>
                    @else
                        <a href="{{ route('test.show', $test->slug) }}" class="inline-flex items-center rounded-full border px-4 py-2 text-sm font-semibold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-500 {{ request()->routeIs('test.show') ? 'border-emerald-200 bg-emerald-500 text-white shadow-sm hover:bg-emerald-600 hover:border-emerald-300' : 'border-slate-200 bg-white text-slate-700 hover:-translate-y-0.5 hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700' }}">Easy</a>
                        <a href="{{ route('test.select', $test->slug) }}" class="inline-flex items-center rounded-full border px-4 py-2 text-sm font-semibold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-500 {{ request()->routeIs('test.select') ? 'border-emerald-200 bg-emerald-500 text-white shadow-sm hover:bg-emerald-600 hover:border-emerald-300' : 'border-slate-200 bg-white text-slate-700 hover:-translate-y-0.5 hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700' }}">Medium</a>
                        <a href="{{ route('test.builder', $test->slug) }}" class="inline-flex items-center rounded-full border px-4 py-2 text-sm font-semibold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-500 {{ request()->routeIs('test.builder') ? 'border-emerald-200 bg-emerald-500 text-white shadow-sm hover:bg-emerald-600 hover:border-emerald-300' : 'border-slate-200 bg-white text-slate-700 hover:-translate-y-0.5 hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700' }}">Builder</a>
                        <a href="{{ route('test.input', $test->slug) }}" class="inline-flex items-center rounded-full border px-4 py-2 text-sm font-semibold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-500 {{ request()->routeIs('test.input') ? 'border-emerald-200 bg-emerald-500 text-white shadow-sm hover:bg-emerald-600 hover:border-emerald-300' : 'border-slate-200 bg-white text-slate-700 hover:-translate-y-0.5 hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700' }}">Hard</a>
                        <a href="{{ route('test.manual', $test->slug) }}" class="inline-flex items-center rounded-full border px-4 py-2 text-sm font-semibold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-500 {{ request()->routeIs('test.manual') ? 'border-emerald-200 bg-emerald-500 text-white shadow-sm hover:bg-emerald-600 hover:border-emerald-300' : 'border-slate-200 bg-white text-slate-700 hover:-translate-y-0.5 hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700' }}">Expert</a>
                    @endif
                </div>
            </div>

            <div class="flex flex-1 flex-col gap-2 md:flex-row md:items-center md:justify-end md:gap-3">
                <span class="flex items-center text-sm font-semibold text-slate-600">
                    <svg class="h-4 w-4 text-rose-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M10 2a8 8 0 1 0 0 16 8 8 0 0 0 0-16Zm.75 4a.75.75 0 0 0-1.5 0v3.25c0 .414.336.75.75.75H13a.75.75 0 0 0 0-1.5h-2.25V6Z" />
                    </svg>
                    <span class="ml-2">Games</span>
                </span>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('saved-test.js.drag-drop', $test->slug) }}" class="inline-flex items-center rounded-full border px-4 py-2 text-sm font-semibold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-rose-500 {{ request()->routeIs('saved-test.js.drag-drop') ? 'border-rose-200 bg-rose-500 text-white shadow-sm hover:bg-rose-600 hover:border-rose-300' : 'border-slate-200 bg-white text-slate-700 hover:-translate-y-0.5 hover:border-rose-200 hover:bg-rose-50 hover:text-rose-700' }}">Drag &amp; Drop</a>
                    <a href="{{ route('saved-test.js.match', $test->slug) }}" class="inline-flex items-center rounded-full border px-4 py-2 text-sm font-semibold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-rose-500 {{ request()->routeIs('saved-test.js.match') ? 'border-rose-200 bg-rose-500 text-white shadow-sm hover:bg-rose-600 hover:border-rose-300' : 'border-slate-200 bg-white text-slate-700 hover:-translate-y-0.5 hover:border-rose-200 hover:bg-rose-50 hover:text-rose-700' }}">Match</a>
                    <a href="{{ route('saved-test.js.dialogue', $test->slug) }}" class="inline-flex items-center rounded-full border px-4 py-2 text-sm font-semibold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-rose-500 {{ request()->routeIs('saved-test.js.dialogue') ? 'border-rose-200 bg-rose-500 text-white shadow-sm hover:bg-rose-600 hover:border-rose-300' : 'border-slate-200 bg-white text-slate-700 hover:-translate-y-0.5 hover:border-rose-200 hover:bg-rose-50 hover:text-rose-700' }}">Dialogue</a>
                </div>
            </div>
        </div>
    </div>
</nav>
