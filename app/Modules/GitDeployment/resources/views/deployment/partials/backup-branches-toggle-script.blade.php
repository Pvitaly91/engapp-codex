@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var toggleButton = document.getElementById('toggle-backup-branches');
                var content = document.getElementById('backup-branches-content');
                var toggleText = document.getElementById('toggle-backup-branches-text');
                var toggleIcon = document.getElementById('toggle-backup-branches-icon');
                
                if (!toggleButton || !content || !toggleText || !toggleIcon) {
                    return;
                }
                
                // Load saved state from localStorage
                var storageKey = 'backup-branches-expanded';
                var savedState = localStorage.getItem(storageKey);
                var isExpanded = savedState === null ? true : savedState === 'true';
                
                // Apply initial state
                if (!isExpanded) {
                    content.style.display = 'none';
                    toggleButton.setAttribute('aria-expanded', 'false');
                    toggleText.textContent = 'Розгорнути';
                    toggleIcon.style.transform = 'rotate(-90deg)';
                }
                
                // Toggle function
                toggleButton.addEventListener('click', function () {
                    isExpanded = !isExpanded;
                    
                    if (isExpanded) {
                        content.style.display = 'block';
                        toggleButton.setAttribute('aria-expanded', 'true');
                        toggleText.textContent = 'Згорнути';
                        toggleIcon.style.transform = 'rotate(0deg)';
                    } else {
                        content.style.display = 'none';
                        toggleButton.setAttribute('aria-expanded', 'false');
                        toggleText.textContent = 'Розгорнути';
                        toggleIcon.style.transform = 'rotate(-90deg)';
                    }
                    
                    // Save state to localStorage
                    localStorage.setItem(storageKey, isExpanded.toString());
                });
            });
        </script>
    @endpush
@endonce
