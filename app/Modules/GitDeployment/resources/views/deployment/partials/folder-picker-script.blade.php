<script>
  function addFolderToList(containerId, folder) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    // Перевіряємо чи папка вже додана
    const existingInputs = container.querySelectorAll('input[name="paths[]"]');
    for (const input of existingInputs) {
      if (input.value === folder) {
        // Папка вже є - не додаємо дублікат
        return;
      }
    }
    
    // Додаємо нову папку
    addPathInput(containerId, folder);
  }
  
  function addPathInput(containerId, value = '') {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    const wrapper = document.createElement('div');
    wrapper.className = 'flex items-center gap-2';
    
    const input = document.createElement('input');
    input.type = 'text';
    input.name = 'paths[]';
    input.value = value;
    input.placeholder = 'app/Modules/Example';
    input.className = 'flex-1 rounded-xl border border-input bg-background px-3 py-2 text-sm font-mono';
    
    const removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.className = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-destructive hover:bg-destructive/10 transition';
    removeBtn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
    removeBtn.onclick = function() {
      wrapper.remove();
    };
    
    wrapper.appendChild(input);
    wrapper.appendChild(removeBtn);
    container.appendChild(wrapper);
    
    if (!value) {
      input.focus();
    }
  }
</script>
<style>
  .folder-tree-item .folder-chevron {
    transform: rotate(0deg);
  }
  .folder-tree-item.expanded .folder-chevron {
    transform: rotate(90deg);
  }
  .folder-tree-item.expanded .folder-children {
    display: block !important;
  }
</style>
