@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var copyButtons = document.querySelectorAll('[data-copy-branch]');

                if (!copyButtons || copyButtons.length === 0) {
                    return;
                }

                var fallbackCopyText = function (text) {
                    return new Promise(function (resolve, reject) {
                        var textarea = document.createElement('textarea');
                        textarea.value = text;
                        textarea.setAttribute('readonly', 'readonly');
                        textarea.style.position = 'absolute';
                        textarea.style.left = '-9999px';
                        textarea.style.top = (window.pageYOffset || document.documentElement.scrollTop || 0) + 'px';

                        document.body.appendChild(textarea);

                        var selection = document.getSelection ? document.getSelection() : null;
                        var selectedRange = null;

                        if (selection && selection.rangeCount > 0) {
                            selectedRange = selection.getRangeAt(0);
                        }

                        textarea.select();
                        textarea.setSelectionRange(0, textarea.value.length);

                        try {
                            var successful = document.execCommand('copy');

                            if (!successful) {
                                throw new Error('Copy command was unsuccessful');
                            }

                            resolve();
                        } catch (error) {
                            reject(error);
                        } finally {
                            document.body.removeChild(textarea);

                            if (selection) {
                                selection.removeAllRanges();

                                if (selectedRange) {
                                    selection.addRange(selectedRange);
                                }
                            }
                        }
                    });
                };

                var resetFeedback = function (button) {
                    if (!button) {
                        return;
                    }

                    var textSpan = button.querySelector('[data-copy-branch-text]');
                    var successSpan = button.querySelector('[data-copy-branch-success]');
                    var errorSpan = button.querySelector('[data-copy-branch-error]');

                    if (textSpan) {
                        textSpan.classList.remove('hidden');
                    }

                    if (successSpan) {
                        successSpan.classList.add('hidden');
                    }

                    if (errorSpan) {
                        errorSpan.classList.add('hidden');
                    }

                    if (button.dataset.copyBranchTimeoutId) {
                        window.clearTimeout(Number(button.dataset.copyBranchTimeoutId));
                        delete button.dataset.copyBranchTimeoutId;
                    }

                    delete button.dataset.copyBranchState;
                };

                var showFeedback = function (button, state) {
                    if (!button) {
                        return;
                    }

                    var textSpan = button.querySelector('[data-copy-branch-text]');
                    var successSpan = button.querySelector('[data-copy-branch-success]');
                    var errorSpan = button.querySelector('[data-copy-branch-error]');

                    if (textSpan) {
                        textSpan.classList.add('hidden');
                    }

                    if (successSpan) {
                        successSpan.classList.toggle('hidden', state !== 'success');
                    }

                    if (errorSpan) {
                        errorSpan.classList.toggle('hidden', state !== 'error');
                    }

                    button.dataset.copyBranchState = state;

                    if (button.dataset.copyBranchTimeoutId) {
                        window.clearTimeout(Number(button.dataset.copyBranchTimeoutId));
                    }

                    var timeoutId = window.setTimeout(function () {
                        resetFeedback(button);
                    }, 2000);

                    button.dataset.copyBranchTimeoutId = String(timeoutId);
                };

                var copyBranchName = function (button) {
                    if (!button) {
                        return;
                    }

                    var branchName = button.dataset.copyBranch || '';

                    if (!branchName) {
                        return;
                    }

                    var performCopy = function () {
                        if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
                            return navigator.clipboard.writeText(branchName);
                        }

                        return fallbackCopyText(branchName);
                    };

                    performCopy()
                        .then(function () {
                            showFeedback(button, 'success');
                        })
                        .catch(function () {
                            fallbackCopyText(branchName)
                                .then(function () {
                                    showFeedback(button, 'success');
                                })
                                .catch(function () {
                                    showFeedback(button, 'error');
                                    window.prompt('Скопіюйте назву гілки вручну:', branchName);
                                });
                        });
                };

                copyButtons.forEach(function (button) {
                    resetFeedback(button);

                    button.addEventListener('click', function () {
                        copyBranchName(button);
                    });
                });
            });
        </script>
    @endpush
@endonce
