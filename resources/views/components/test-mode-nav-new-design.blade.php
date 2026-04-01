@php
    $routeName = request()->route()?->getName() ?? '';
    $isStepMode = request()->routeIs(
        'test.step',
        'test.step-select',
        'test.step-input',
        'test.step-manual'
    );

    $difficulty = match (true) {
        request()->routeIs('test.select', 'test.step-select') => 'medium',
        request()->routeIs('test.input', 'test.step-input') => 'hard',
        request()->routeIs('test.manual', 'test.step-manual') => 'expert',
        default => 'easy',
    };

    $cardRouteName = match ($difficulty) {
        'medium' => 'test.select',
        'hard' => 'test.input',
        'expert' => 'test.manual',
        default => 'test.show',
    };

    $stepRouteName = match ($difficulty) {
        'medium' => 'test.step-select',
        'hard' => 'test.step-input',
        'expert' => 'test.step-manual',
        default => 'test.step',
    };

    $difficultyRoutes = $isStepMode
        ? [
            'easy' => 'test.step',
            'medium' => 'test.step-select',
            'hard' => 'test.step-input',
            'expert' => 'test.step-manual',
        ]
        : [
            'easy' => 'test.show',
            'medium' => 'test.select',
            'hard' => 'test.input',
            'expert' => 'test.manual',
        ];

    $preservedQuery = request()->only(['filters', 'name', 'launch']);
    $testRoute = static function (string $name) use ($test, $preservedQuery): string {
        $url = localized_route($name, $test->slug);

        return $preservedQuery === []
            ? $url
            : $url . '?' . http_build_query($preservedQuery);
    };

    $pillClass = static function (bool $active, string $activeTheme = 'solid'): string {
        if ($active && $activeTheme === 'soft') {
            return 'border-color: var(--accent); background: var(--accent-soft); color: var(--text);';
        }

        if ($active) {
            return 'border-color: var(--accent); background: var(--accent); color: white;';
        }

        return 'border-color: var(--line); color: var(--text);';
    };
@endphp

<nav class="mt-10 mb-10">
    <div class="rounded-[28px] border p-6 shadow-card surface-card-strong" style="border-color: var(--line);">
        <div class="grid gap-6 xl:grid-cols-3">
            <div class="space-y-4">
                <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('frontend.tests.mode.view') }}</p>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ $testRoute($cardRouteName) }}"
                       class="inline-flex items-center rounded-full border px-4 py-2 text-sm font-semibold transition"
                       style="{{ $pillClass(! $isStepMode && ! request()->routeIs('test.drag-drop', 'test.match', 'test.dialogue')) }}">
                        {{ __('frontend.tests.mode.card') }}
                    </a>
                    <a href="{{ $testRoute($stepRouteName) }}"
                       class="inline-flex items-center rounded-full border px-4 py-2 text-sm font-semibold transition"
                       style="{{ $pillClass($isStepMode) }}">
                        {{ __('frontend.tests.mode.step') }}
                    </a>
                </div>
            </div>

            <div class="space-y-4">
                <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('frontend.tests.mode.difficulty') }}</p>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ $testRoute($difficultyRoutes['easy']) }}"
                       class="inline-flex items-center rounded-full border px-4 py-2 text-sm font-semibold transition"
                       style="{{ $pillClass($routeName === $difficultyRoutes['easy'], 'soft') }}">
                        {{ __('frontend.tests.mode.easy') }}
                    </a>
                    <a href="{{ $testRoute($difficultyRoutes['medium']) }}"
                       class="inline-flex items-center rounded-full border px-4 py-2 text-sm font-semibold transition"
                       style="{{ $pillClass($routeName === $difficultyRoutes['medium'], 'soft') }}">
                        {{ __('frontend.tests.mode.medium') }}
                    </a>
                    <a href="{{ $testRoute($difficultyRoutes['hard']) }}"
                       class="inline-flex items-center rounded-full border px-4 py-2 text-sm font-semibold transition"
                       style="{{ $pillClass($routeName === $difficultyRoutes['hard'], 'soft') }}">
                        {{ __('frontend.tests.mode.hard') }}
                    </a>
                    <a href="{{ $testRoute($difficultyRoutes['expert']) }}"
                       class="inline-flex items-center rounded-full border px-4 py-2 text-sm font-semibold transition"
                       style="{{ $pillClass($routeName === $difficultyRoutes['expert'], 'soft') }}">
                        {{ __('frontend.tests.mode.expert') }}
                    </a>
                </div>
            </div>

            <div class="space-y-4">
                <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('frontend.tests.mode.games') }}</p>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ $testRoute('test.drag-drop') }}"
                       class="inline-flex items-center rounded-full border px-4 py-2 text-sm font-semibold transition"
                       style="{{ $pillClass(request()->routeIs('test.drag-drop')) }}">
                        {{ __('frontend.tests.mode.drag_drop') }}
                    </a>
                    <a href="{{ $testRoute('test.match') }}"
                       class="inline-flex items-center rounded-full border px-4 py-2 text-sm font-semibold transition"
                       style="{{ $pillClass(request()->routeIs('test.match')) }}">
                        {{ __('frontend.tests.mode.match') }}
                    </a>
                    <a href="{{ $testRoute('test.dialogue') }}"
                       class="inline-flex items-center rounded-full border px-4 py-2 text-sm font-semibold transition"
                       style="{{ $pillClass(request()->routeIs('test.dialogue')) }}">
                        {{ __('frontend.tests.mode.dialogue') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</nav>
