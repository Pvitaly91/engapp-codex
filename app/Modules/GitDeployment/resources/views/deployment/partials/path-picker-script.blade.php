@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                function addSelectedPath(selectId, textareaId) {
                    var select = document.getElementById(selectId);
                    var textarea = document.getElementById(textareaId);

                    if (!select || !textarea) {
                        return;
                    }

                    var path = select.value;
                    if (!path) {
                        return;
                    }

                    var existing = textarea.value
                        .split(/[\r\n,;]+/)
                        .map(function (item) { return item.trim(); })
                        .filter(function (item) { return item.length > 0; });

                    if (existing.indexOf(path) === -1) {
                        existing.push(path);
                    }

                    textarea.value = existing.join("\n");
                }

                document.querySelectorAll('[data-path-picker]')
                    .forEach(function (button) {
                        button.addEventListener('click', function () {
                            addSelectedPath(
                                button.getAttribute('data-path-select'),
                                button.getAttribute('data-target-textarea')
                            );
                        });
                    });

                document.querySelectorAll('[data-path-select]').forEach(function (select) {
                    select.addEventListener('dblclick', function () {
                        var target = select.getAttribute('data-target-textarea');
                        addSelectedPath(select.id, target);
                    });
                });
            });
        </script>
    @endpush
@endonce
