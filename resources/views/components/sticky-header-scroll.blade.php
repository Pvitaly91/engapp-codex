<script>
/**
 * Sticky header scroll detection - adds 'is-stuck' class when header is sticky
 */
(function() {
  const stickyHeader = document.getElementById('sticky-header');
  if (!stickyHeader) return;

  let lastScrollY = 0;
  let ticking = false;

  function updateStickyState() {
    const rect = stickyHeader.getBoundingClientRect();
    // Header is "stuck" when its top is at or near the viewport top
    const isStuck = rect.top <= 1 && window.scrollY > 50;
    
    if (isStuck) {
      stickyHeader.classList.add('is-stuck');
    } else {
      stickyHeader.classList.remove('is-stuck');
    }
    ticking = false;
  }

  function onScroll() {
    lastScrollY = window.scrollY;
    if (!ticking) {
      window.requestAnimationFrame(updateStickyState);
      ticking = true;
    }
  }

  window.addEventListener('scroll', onScroll, { passive: true });
  // Initial check
  updateStickyState();
})();
</script>
