<script>
window.JS_TEST_PERSISTENCE = {
    endpoint: '{{ route('saved-test.js.state', $test->slug) }}',
    mode: '{{ $mode }}',
    token: '{{ csrf_token() }}',
    saved: @json($savedState),
};
</script>
