@php
    $routeName = request()->route()?->getName() ?? '';
    $isAdminMode = \Illuminate\Support\Str::startsWith($routeName, 'saved-test.js.');
    $isStepMode = $isAdminMode
        ? request()->routeIs('saved-test.js.step', 'saved-test.js.step-select', 'saved-test.js.step-input', 'saved-test.js.step-manual')
        : request()->routeIs('test.step', 'test.step-select', 'test.step-input', 'test.step-manual');

    $difficulty = match (true) {
        request()->routeIs('test.select', 'test.step-select', 'saved-test.js.select', 'saved-test.js.step-select') => 'medium',
        request()->routeIs('test.input', 'test.step-input', 'saved-test.js.input', 'saved-test.js.step-input') => 'hard',
        request()->routeIs('test.manual', 'test.step-manual', 'saved-test.js.manual', 'saved-test.js.step-manual') => 'expert',
        default => 'easy',
    };

    $cardRouteName = match ($difficulty) {
        'medium' => $isAdminMode ? 'saved-test.js.select' : 'test.select',
        'hard' => $isAdminMode ? 'saved-test.js.input' : 'test.input',
        'expert' => $isAdminMode ? 'saved-test.js.manual' : 'test.manual',
        default => $isAdminMode ? 'saved-test.js.show' : 'test.show',
    };

    $stepRouteName = match ($difficulty) {
        'medium' => $isAdminMode ? 'saved-test.js.step-select' : 'test.step-select',
        'hard' => $isAdminMode ? 'saved-test.js.step-input' : 'test.step-input',
        'expert' => $isAdminMode ? 'saved-test.js.step-manual' : 'test.step-manual',
        default => $isAdminMode ? 'saved-test.js.step' : 'test.step',
    };

    $difficultyRoutes = $isStepMode
        ? [
            'easy' => $isAdminMode ? 'saved-test.js.step' : 'test.step',
            'medium' => $isAdminMode ? 'saved-test.js.step-select' : 'test.step-select',
            'hard' => $isAdminMode ? 'saved-test.js.step-input' : 'test.step-input',
            'expert' => $isAdminMode ? 'saved-test.js.step-manual' : 'test.step-manual',
        ]
        : [
            'easy' => $isAdminMode ? 'saved-test.js.show' : 'test.show',
            'medium' => $isAdminMode ? 'saved-test.js.select' : 'test.select',
            'hard' => $isAdminMode ? 'saved-test.js.input' : 'test.input',
            'expert' => $isAdminMode ? 'saved-test.js.manual' : 'test.manual',
        ];

    $gameRoutes = [
        'drag_drop' => $isAdminMode ? 'saved-test.js.drag-drop' : 'test.drag-drop',
        'match' => $isAdminMode ? 'saved-test.js.match' : 'test.match',
        'dialogue' => $isAdminMode ? 'saved-test.js.dialogue' : 'test.dialogue',
    ];
@endphp

<nav class="mb-6 text-sm space-y-2">
    <div class="flex items-center gap-2">
        <span class="font-semibold">{{ __('frontend.tests.mode.view') }}:</span>
        <a href="{{ localized_route($cardRouteName, $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ ! $isStepMode ? 'bg-stone-900 text-white' : '' }}">{{ __('frontend.tests.mode.card') }}</a>
        <a href="{{ localized_route($stepRouteName, $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ $isStepMode ? 'bg-stone-900 text-white' : '' }}">{{ __('frontend.tests.mode.step') }}</a>
    </div>
    <div class="flex items-center gap-2">
        <span class="font-semibold">{{ __('frontend.tests.mode.difficulty') }}:</span>
        <a href="{{ localized_route($difficultyRoutes['easy'], $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ $routeName === $difficultyRoutes['easy'] ? 'bg-stone-900 text-white' : '' }}">{{ __('frontend.tests.mode.easy') }}</a>
        <a href="{{ localized_route($difficultyRoutes['medium'], $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ $routeName === $difficultyRoutes['medium'] ? 'bg-stone-900 text-white' : '' }}">{{ __('frontend.tests.mode.medium') }}</a>
        <a href="{{ localized_route($difficultyRoutes['hard'], $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ $routeName === $difficultyRoutes['hard'] ? 'bg-stone-900 text-white' : '' }}">{{ __('frontend.tests.mode.hard') }}</a>
        <a href="{{ localized_route($difficultyRoutes['expert'], $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ $routeName === $difficultyRoutes['expert'] ? 'bg-stone-900 text-white' : '' }}">{{ __('frontend.tests.mode.expert') }}</a>
    </div>
    <div class="flex items-center gap-2">
        <span class="font-semibold">{{ __('frontend.tests.mode.games') }}:</span>
        <a href="{{ localized_route($gameRoutes['drag_drop'], $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ request()->routeIs($gameRoutes['drag_drop']) ? 'bg-stone-900 text-white' : '' }}">{{ __('frontend.tests.mode.drag_drop') }}</a>
        <a href="{{ localized_route($gameRoutes['match'], $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ request()->routeIs($gameRoutes['match']) ? 'bg-stone-900 text-white' : '' }}">{{ __('frontend.tests.mode.match') }}</a>
        <a href="{{ localized_route($gameRoutes['dialogue'], $test->slug) }}" class="px-3 py-1 rounded-lg border border-stone-300 {{ request()->routeIs($gameRoutes['dialogue']) ? 'bg-stone-900 text-white' : '' }}">{{ __('frontend.tests.mode.dialogue') }}</a>
    </div>
</nav>
