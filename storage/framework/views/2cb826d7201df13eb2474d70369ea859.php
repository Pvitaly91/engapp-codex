<script>
window.JS_TEST_PERSISTENCE = {
    endpoint: '<?php echo e(route('saved-test.js.state', $test->slug)); ?>',
    mode: '<?php echo e($mode); ?>',
    token: '<?php echo e(csrf_token()); ?>',
    questionsEndpoint: '<?php echo e(route('saved-test.js.questions', $test->slug)); ?>',
    saved: <?php echo json_encode($savedState, 15, 512) ?>,
};
</script>
<?php /**PATH /var/www/pvital01/data/www/gramlyze.com/resources/views/components/saved-test-js-persistence.blade.php ENDPATH**/ ?>