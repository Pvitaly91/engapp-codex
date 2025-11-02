<script>
  document.addEventListener('DOMContentLoaded', function () {
    var clearButtons = document.querySelectorAll('[data-branch-clear]');

    clearButtons.forEach(function (button) {
      var targetId = button.getAttribute('data-branch-clear');
      if (!targetId) {
        return;
      }

      var input = document.getElementById(targetId);
      if (!input) {
        return;
      }

      var updateVisibility = function () {
        var hasValue = input.value && input.value.trim() !== '';
        button.classList.toggle('opacity-0', !hasValue);
        button.classList.toggle('pointer-events-none', !hasValue);
      };

      updateVisibility();

      input.addEventListener('input', updateVisibility);

      button.addEventListener('click', function () {
        input.value = '';
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.focus();
        updateVisibility();
      });
    });
  });
</script>
