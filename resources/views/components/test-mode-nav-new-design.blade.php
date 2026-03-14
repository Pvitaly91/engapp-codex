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

<nav class="mt-8 mb-8">
    <div class="rounded-[28px] border p-5 shadow-card surface-card-strong" style="border-color: var(--line);">
        <div class="grid gap-5 xl:grid-cols-3">
            <div class="space-y-3">
                <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">View mode</p>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ localized_route($cardRouteName, $test->slug) }}"
                       class="inline-flex items-center rounded-full border px-4 py-2 text-sm font-semibold transition"
                       style="{{ $pillClass(! $isStepMode && ! request()->routeIs('test.drag-drop', 'test.match', 'test.dialogue')) }}">
                        Card
                    </a>
                    <a href="{{ localized_route($stepRouteName, $test->slug) }}"
                       class="inline-flex items-center rounded-full border px-4 py-2 text-sm font-semibold transition"
                       style="{{ $pillClass($isStepMode) }}">
                        Step
                    </a>
                </div>
            </div>

            <div class="space-y-3">
                <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">Difficulty</p>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ localized_route($difficultyRoutes['easy'], $test->slug) }}"
                       class="inline-flex items-center rounded-full border px-4 py-2 text-sm font-semibold transition"
                       style="{{ $pillClass($routeName === $difficultyRoutes['easy'], 'soft') }}">
                        Easy
                    </a>
                    <a href="{{ localized_route($difficultyRoutes['medium'], $test->slug) }}"
                       class="inline-flex items-center rounded-full border px-4 py-2 text-sm font-semibold transition"
                       style="{{ $pillClass($routeName === $difficultyRoutes['medium'], 'soft') }}">
                        Medium
                    </a>
                    <a href="{{ localized_route($difficultyRoutes['hard'], $test->slug) }}"
                       class="inline-flex items-center rounded-full border px-4 py-2 text-sm font-semibold transition"
                       style="{{ $pillClass($routeName === $difficultyRoutes['hard'], 'soft') }}">
                        Hard
                    </a>
                    <a href="{{ localized_route($difficultyRoutes['expert'], $test->slug) }}"
                       class="inline-flex items-center rounded-full border px-4 py-2 text-sm font-semibold transition"
                       style="{{ $pillClass($routeName === $difficultyRoutes['expert'], 'soft') }}">
                        Expert
                    </a>
                </div>
            </div>

            <div class="space-y-3">
                <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">Games</p>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ localized_route('test.drag-drop', $test->slug) }}"
                       class="inline-flex items-center rounded-full border px-4 py-2 text-sm font-semibold transition"
                       style="{{ $pillClass(request()->routeIs('test.drag-drop')) }}">
                        Drag &amp; Drop
                    </a>
                    <a href="{{ localized_route('test.match', $test->slug) }}"
                       class="inline-flex items-center rounded-full border px-4 py-2 text-sm font-semibold transition"
                       style="{{ $pillClass(request()->routeIs('test.match')) }}">
                        Match
                    </a>
                    <a href="{{ localized_route('test.dialogue', $test->slug) }}"
                       class="inline-flex items-center rounded-full border px-4 py-2 text-sm font-semibold transition"
                       style="{{ $pillClass(request()->routeIs('test.dialogue')) }}">
                        Dialogue
                    </a>
                </div>
            </div>
        </div>
    </div>
</nav>
