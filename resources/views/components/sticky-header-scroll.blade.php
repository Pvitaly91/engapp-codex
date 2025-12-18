<style>
@media (min-width: 1024px) {
  #sticky-header {
    --sticky-transition: 200ms;
  }

  #sticky-header .sticky-inner {
    transition: padding var(--sticky-transition) ease, margin var(--sticky-transition) ease, transform var(--sticky-transition) ease;
    transform-origin: top;
    will-change: padding, transform;
  }

  #sticky-header .progress-section,
  #sticky-header .progress-bar-container,
  #sticky-header .progress-icon,
  #sticky-header .progress-value,
  #sticky-header .progress-label-text,
  #sticky-header .word-search-section input,
  #sticky-header .word-search-section label,
  #sticky-header .word-search-section span {
    transition: padding var(--sticky-transition) ease, margin var(--sticky-transition) ease, font-size var(--sticky-transition) ease, height var(--sticky-transition) ease, gap var(--sticky-transition) ease, transform var(--sticky-transition) ease;
  }

  #sticky-header.is-stuck .sticky-inner {
    padding: 0.75rem 1rem;
    transform: scaleY(0.9);
  }

  #sticky-header.is-stuck .sticky-inner > * + * {
    margin-top: 0.5rem;
  }

  #sticky-header.is-stuck .word-search-section input {
    padding: 0.6rem 0.9rem;
    font-size: 0.95rem;
    border-radius: 0.9rem;
  }

  #sticky-header.is-stuck .word-search-section label,
  #sticky-header.is-stuck .word-search-section span {
    font-size: 0.7rem;
  }

  #sticky-header.is-stuck .progress-section {
    padding: 0.75rem 0.9rem;
  }

  #sticky-header.is-stuck .progress-icon {
    width: 1.75rem;
    height: 1.75rem;
  }

  #sticky-header.is-stuck .progress-label-text {
    font-size: 0.7rem;
  }

  #sticky-header.is-stuck .progress-value {
    font-size: 1.05rem;
  }

  #sticky-header.is-stuck .progress-bar-container {
    height: 0.45rem;
  }

  #sticky-header.is-stuck #progress-bar {
    height: 100%;
  }
}

.sticky-header-spacer {
  display: none;
  pointer-events: none;
}
</style>

<script>
/**
 * Sticky header scroll detection - adds 'is-stuck' class when header is sticky.
 * Desktop-only behavior with spacer to prevent layout shift.
 */
(function() {
  const stickyHeader = document.getElementById('sticky-header');
  if (!stickyHeader) return;

  const desktopQuery = window.matchMedia('(min-width: 1024px)');
  const spacer = document.createElement('div');
  spacer.className = 'sticky-header-spacer';
  spacer.setAttribute('aria-hidden', 'true');
  stickyHeader.insertAdjacentElement('afterend', spacer);

  let ticking = false;
  let isStuck = false;
  let baseHeight = stickyHeader.offsetHeight;

  function updateBaseHeight() {
    if (isStuck) return;
    baseHeight = stickyHeader.offsetHeight;
    spacer.style.height = `${baseHeight}px`;
  }

  function applyState(stuck) {
    if (stuck === isStuck) return;
    isStuck = stuck;
    stickyHeader.classList.toggle('is-stuck', stuck);
    spacer.style.display = stuck ? 'block' : 'none';
    if (stuck) {
      spacer.style.height = `${baseHeight}px`;
    } else {
      updateBaseHeight();
    }
  }

  function updateStickyState() {
    if (!desktopQuery.matches) {
      applyState(false);
      ticking = false;
      return;
    }

    const rect = stickyHeader.getBoundingClientRect();
    const stuckNow = rect.top <= 1 && window.scrollY > 50;
    applyState(stuckNow);
    ticking = false;
  }

  function onScroll() {
    if (!ticking) {
      window.requestAnimationFrame(updateStickyState);
      ticking = true;
    }
  }

  window.addEventListener('scroll', onScroll, { passive: true });

  window.addEventListener('resize', () => {
    if (!desktopQuery.matches) {
      applyState(false);
      return;
    }
    window.requestAnimationFrame(() => {
      updateBaseHeight();
      updateStickyState();
    });
  }, { passive: true });

  desktopQuery.addEventListener('change', (event) => {
    if (event.matches) {
      updateBaseHeight();
      updateStickyState();
    } else {
      applyState(false);
    }
  });

  // Initial setup
  updateBaseHeight();
  updateStickyState();
})();
</script>
