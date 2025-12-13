@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                function addPath(path, textareaId) {
                    var textarea = document.getElementById(textareaId);

                    if (!textarea || !path) {
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

                function toggleNode(button) {
                    var node = button.closest('[data-tree-node]');
                    if (!node) {
                        return;
                    }

                    var children = null;
                    for (var i = 0; i < node.children.length; i++) {
                        var child = node.children[i];
                        if (child.hasAttribute && child.hasAttribute('data-tree-children')) {
                            children = child;
                            break;
                        }
                    }

                    if (!children) {
                        return;
                    }

                    var isHidden = children.classList.toggle('hidden');
                    var icon = button.querySelector('[data-tree-icon]');

                    button.setAttribute('aria-expanded', isHidden ? 'false' : 'true');

                    if (icon) {
                        icon.textContent = isHidden ? '▸' : '▾';
                    }
                }

                document.querySelectorAll('[data-path-tree]').forEach(function (tree) {
                    var textareaId = tree.getAttribute('data-target-textarea');

                    tree.addEventListener('click', function (event) {
                        var toggle = event.target.closest('[data-tree-toggle]');
                        if (toggle && tree.contains(toggle)) {
                            toggleNode(toggle);
                            event.preventDefault();
                            return;
                        }

                        var addButton = event.target.closest('[data-path-add]');
                        if (addButton && tree.contains(addButton)) {
                            var path = addButton.getAttribute('data-path');
                            addPath(path, textareaId);
                        }
                    });

                    tree.querySelectorAll('[data-path-add]').forEach(function (addButton) {
                        addButton.addEventListener('dblclick', function () {
                            var path = addButton.getAttribute('data-path');
                            addPath(path, textareaId);
                        });
                    });
                });
            });
        </script>
    @endpush
@endonce
