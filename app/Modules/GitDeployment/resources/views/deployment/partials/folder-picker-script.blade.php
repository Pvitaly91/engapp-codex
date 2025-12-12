<script>
  function addFolderToTextarea(textareaId, folder) {
    const textarea = document.getElementById(textareaId);
    if (!textarea) return;
    
    const currentValue = textarea.value.trim();
    const existingPaths = currentValue ? currentValue.split('\n').map(p => p.trim()).filter(p => p) : [];
    
    // Перевіряємо чи папка вже додана
    if (existingPaths.includes(folder)) {
      // Якщо так - видаляємо її
      const newPaths = existingPaths.filter(p => p !== folder);
      textarea.value = newPaths.join('\n');
    } else {
      // Якщо ні - додаємо
      existingPaths.push(folder);
      textarea.value = existingPaths.join('\n');
    }
    
    // Фокусуємо textarea
    textarea.focus();
  }
</script>
