@php
    $routeName = request()->route()?->getName() ?? '';
    $isAdminRoute = ($isAdmin ?? false)
        || \Illuminate\Support\Str::startsWith($routeName, 'saved-test.');

    $virtualQuery = request()->only(['filters', 'name', 'launch']);
    $stateEndpoint = $isAdminRoute
        ? route('saved-test.js.state', $test->slug)
        : localized_route('test.js.state', $test->slug);
    $questionsEndpoint = $isAdminRoute
        ? route('saved-test.js.questions', $test->slug)
        : localized_route('test.js.questions', $test->slug);

    if ($virtualQuery !== []) {
        $queryString = http_build_query($virtualQuery);
        $stateEndpoint .= '?' . $queryString;
        $questionsEndpoint .= '?' . $queryString;
    }

    $storageKey = 'gramlyze:js-test-state:v2:' . hash('sha256', implode('|', [
        request()->getHost(),
        $test->slug,
        $mode,
    ]));
    $legacyStorageKey = 'gramlyze:js-test-state:v1:' . hash('sha256', implode('|', [
        request()->getHost(),
        $test->slug,
        $mode,
        $stateEndpoint,
    ]));
    $stateNamespace = \App\Support\FinitePracticeQualityCatalogs::stateNamespace(
        is_array($test->filters ?? null) ? $test->filters : [], app()->getLocale(), (string) ($test->uuid ?? '')
    );
    $storageKeys = array_values(array_unique([$storageKey, $legacyStorageKey]));
    if ($stateNamespace !== null) {
        $storageKey .= ':'.$stateNamespace;
        // The existing persistence helper deletes listed legacy keys after a
        // write. Do not expose old bank/locale keys to that cleanup path.
        $storageKeys = [$storageKey];
    }
@endphp
<script>
window.JS_TEST_PERSISTENCE = {
    endpoint: @json($stateEndpoint),
    mode: '{{ $mode }}',
    token: '{{ csrf_token() }}',
    questionsEndpoint: @json($questionsEndpoint),
    storageKey: @json($storageKey),
    storageKeys: @json($storageKeys),
    saved: @json($savedState),
};
</script>
