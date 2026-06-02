@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Function to make select searchable and allow custom input
                function makeSelectSearchable(selectId) {
                    var select = document.getElementById(selectId);
                    if (!select) {
                        return;
                    }

                    // Create wrapper
                    var wrapper = document.createElement('div');
                    wrapper.className = 'relative';
                    select.parentNode.insertBefore(wrapper, select);
                    wrapper.appendChild(select);

                    // Create input for searching/custom entry
                    var input = document.createElement('input');
                    input.type = 'text';
                    input.placeholder = 'Введіть назву або оберіть з існуючих гілок';
                    input.className = select.className;
                    input.style.display = 'none';

                    // Create dropdown list
                    var dropdown = document.createElement('div');
                    dropdown.className = 'absolute z-50 w-full mt-1 bg-background border border-input rounded-2xl shadow-lg max-h-60 overflow-auto hidden';
                    
                    wrapper.appendChild(input);
                    wrapper.appendChild(dropdown);

                    // Store all options
                    var allOptions = Array.from(select.options).slice(1); // Skip first "-- Оберіть --" option

                    // Toggle between select and input
                    var showInput = function() {
                        select.style.display = 'none';
                        input.style.display = 'block';
                        input.focus();
                    };

                    var showSelect = function() {
                        input.style.display = 'none';
                        select.style.display = 'block';
                        dropdown.classList.add('hidden');
                    };

                    // Update dropdown with filtered options
                    var updateDropdown = function(searchText) {
                        dropdown.innerHTML = '';
                        
                        var filtered = allOptions.filter(function(opt) {
                            return opt.value.toLowerCase().includes(searchText.toLowerCase());
                        });

                        if (filtered.length === 0 && searchText.trim() !== '') {
                            var emptyItem = document.createElement('div');
                            emptyItem.className = 'px-4 py-2 text-sm text-muted-foreground';
                            emptyItem.textContent = 'Введіть нову назву гілки: ' + searchText;
                            dropdown.appendChild(emptyItem);
                        } else {
                            filtered.forEach(function(opt) {
                                var item = document.createElement('div');
                                item.className = 'px-4 py-2 text-sm cursor-pointer hover:bg-muted/50 transition';
                                item.textContent = opt.value;
                                item.addEventListener('click', function() {
                                    input.value = opt.value;
                                    select.value = opt.value;
                                    dropdown.classList.add('hidden');
                                    showSelect();
                                });
                                dropdown.appendChild(item);
                            });
                        }

                        if (dropdown.children.length > 0) {
                            dropdown.classList.remove('hidden');
                        }
                    };

                    // Event listeners
                    select.addEventListener('focus', function() {
                        showInput();
                        input.value = select.value;
                        updateDropdown('');
                    });

                    input.addEventListener('input', function() {
                        updateDropdown(input.value);
                    });

                    input.addEventListener('blur', function(e) {
                        setTimeout(function() {
                            // Update select with custom value if needed
                            var customValue = input.value.trim();
                            if (customValue !== '') {
                                // Check if value exists in options
                                var exists = allOptions.some(function(opt) {
                                    return opt.value === customValue;
                                });
                                
                                if (!exists && customValue !== '') {
                                    // Add new option for custom value
                                    var newOption = document.createElement('option');
                                    newOption.value = customValue;
                                    newOption.textContent = customValue;
                                    newOption.selected = true;
                                    select.appendChild(newOption);
                                } else {
                                    select.value = customValue;
                                }
                            } else {
                                select.value = '';
                            }
                            
                            showSelect();
                        }, 200);
                    });

                    input.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            input.blur();
                        } else if (e.key === 'Escape') {
                            input.value = select.value;
                            showSelect();
                        }
                    });

                    // Close dropdown when clicking outside
                    document.addEventListener('click', function(e) {
                        if (!wrapper.contains(e.target)) {
                            dropdown.classList.add('hidden');
                        }
                    });
                }

                // Initialize searchable selects
                makeSelectSearchable('auto-push-branch');
                makeSelectSearchable('native-auto-push-branch');
            });
        </script>
    @endpush
@endonce
