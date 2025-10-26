<script>
window.JS_TEST_PERSISTENCE = {
    endpoint: '{{ route('saved-test.js.state', $test->slug) }}',
    mode: '{{ $mode }}',
    token: '{{ csrf_token() }}',
    questionsEndpoint: '{{ route('saved-test.js.questions', $test->slug) }}',
    saved: @json($savedState),
};
</script>
