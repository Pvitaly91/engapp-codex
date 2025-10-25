<script>
window.JS_TEST_PERSISTENCE = {
    endpoint: '<?php echo e(route('saved-test.js.state', $test->slug)); ?>',
    mode: '<?php echo e($mode); ?>',
    token: '<?php echo e(csrf_token()); ?>',
    questionsEndpoint: '<?php echo e(route('saved-test.js.questions', $test->slug)); ?>',
    saved: <?php echo json_encode($savedState, 15, 512) ?>,
};
</script>
<?php /**PATH G:\DEV\htdocs\engapp-codex.loc\resources\views/components/saved-test-js-persistence.blade.php ENDPATH**/ ?>