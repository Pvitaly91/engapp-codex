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

    $storageKey = 'gramlyze:js-test-state:v1:' . hash('sha256', implode('|', [
        request()->getHost(),
        $test->slug,
        $mode,
        $stateEndpoint,
    ]));
@endphp
<script>
window.JS_TEST_PERSISTENCE = {
    endpoint: @json($stateEndpoint),
    mode: '{{ $mode }}',
    token: '{{ csrf_token() }}',
    questionsEndpoint: @json($questionsEndpoint),
    storageKey: @json($storageKey),
    saved: @json($savedState),
};
</script>
