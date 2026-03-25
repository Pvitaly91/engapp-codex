<script>
/**
 * Sticky header scroll detection - adds 'is-stuck' class when header is sticky
 * Only active on desktop (viewport width >= 1024px)
 * Includes layout shift prevention via dynamic spacer
 * Includes search toggle functionality when in stuck mode
 */
(function() {
  const stickyHeader = document.getElementById('sticky-header');
  if (!stickyHeader) return;
  const siteHeader = document.getElementById('site-header') || document.querySelector('body > .relative > header');
  const siteHeaderInner = siteHeader ? siteHeader.querySelector('div') : null;
  const catalogShell = document.getElementById('catalog-shell');

  // Desktop media query (min-width: 1024px)
  const desktopQuery = window.matchMedia('(min-width: 1024px)');
  let ticking = false;
  let originalHeight = 0;
  let stuckHeight = 0;
  let stickyTriggerY = 0;
  let spacer = null;

  function syncAttachedTestControls(isAttached) {
    if (siteHeader) {
      siteHeader.classList.toggle('has-attached-test-controls', isAttached);
    }
    if (siteHeaderInner) {
      siteHeaderInner.classList.toggle('has-attached-test-controls', isAttached);
    }
    if (catalogShell) {
      catalogShell.classList.toggle('has-attached-test-controls', isAttached);
    }
  }

  // Create spacer element for layout shift prevention
  function createSpacer() {
    spacer = document.createElement('div');
    spacer.className = 'sticky-header-spacer';
    spacer.style.cssText = 'height: 0; pointer-events: none;';
    stickyHeader.parentNode.insertBefore(spacer, stickyHeader.nextSibling);
  }

  function getSiteHeaderOffset() {
    if (!desktopQuery.matches || !siteHeader) {
      return 0;
    }

    return Math.max(0, Math.round(siteHeader.getBoundingClientRect().bottom) - 1);
  }

  function syncStickyOffset() {
    const offset = getSiteHeaderOffset();
    stickyHeader.style.setProperty('--site-header-offset', offset + 'px');
    stickyHeader.style.top = offset + 'px';
  }

  // Measure heights for compensation calculation
  function measureHeights() {
    // Only measure on desktop
    if (!desktopQuery.matches) {
      stickyTriggerY = 0;
      return;
    }

    syncStickyOffset();

    // Remove is-stuck to measure original height
    const wasStuck = stickyHeader.classList.contains('is-stuck');
    const wasExpanded = stickyHeader.classList.contains('search-expanded');
    stickyHeader.classList.remove('is-stuck', 'search-expanded');
    syncAttachedTestControls(false);

    stickyTriggerY = Math.max(
      0,
      Math.round(stickyHeader.getBoundingClientRect().top + window.scrollY - getSiteHeaderOffset())
    );

    originalHeight = stickyHeader.offsetHeight;

    // Measure stuck height by adding class temporarily
    stickyHeader.classList.add('is-stuck');
    stuckHeight = stickyHeader.offsetHeight;

    // Restore original state
    if (!wasStuck) {
      stickyHeader.classList.remove('is-stuck');
    }
    if (wasExpanded) {
      stickyHeader.classList.add('search-expanded');
    }

    syncAttachedTestControls(wasStuck);

    // Store for CSS use
    stickyHeader.style.setProperty('--sticky-header-original-height', originalHeight + 'px');
    stickyHeader.style.setProperty('--sticky-header-stuck-height', stuckHeight + 'px');
    stickyHeader.style.setProperty('--sticky-header-trigger-y', stickyTriggerY + 'px');
  }

  function updateStickyState() {
    // Only apply sticky behavior on desktop
    if (!desktopQuery.matches) {
      stickyHeader.classList.remove('is-stuck', 'search-expanded');
      syncAttachedTestControls(false);
      stickyHeader.style.removeProperty('--site-header-offset');
      stickyHeader.style.top = '';
      if (spacer) {
        spacer.style.height = '0';
      }
      ticking = false;
      return;
    }

    const isStuck = stickyTriggerY > 0 && window.scrollY >= stickyTriggerY;
    const wasStuck = stickyHeader.classList.contains('is-stuck');

    if (isStuck && !wasStuck) {
      stickyHeader.classList.add('is-stuck');
      syncAttachedTestControls(true);
      // Remove search-expanded when first becoming stuck
      stickyHeader.classList.remove('search-expanded');
      // Add spacer height to compensate for reduced header height
      if (spacer && originalHeight && stuckHeight) {
        const heightDiff = originalHeight - stuckHeight;
        spacer.style.height = heightDiff + 'px';
      }
    } else if (!isStuck && wasStuck) {
      stickyHeader.classList.remove('is-stuck', 'search-expanded');
      syncAttachedTestControls(false);
      if (spacer) {
        spacer.style.height = '0';
      }
    }
    
    ticking = false;
  }

  function onScroll() {
    if (!ticking) {
      window.requestAnimationFrame(updateStickyState);
      ticking = true;
    }
  }

  // Handle viewport resize (desktop <-> mobile)
  let resizeTimeout = null;
  function onResize() {
    // Debounce resize measurements
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(function() {
      syncStickyOffset();
      measureHeights();
      updateStickyState();
    }, 100);
  }

  // Search toggle functionality
  function setupSearchToggle() {
    const searchToggleBtn = document.getElementById('sticky-search-toggle');
    if (!searchToggleBtn) return;

    searchToggleBtn.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      
      // Toggle search-expanded class
      stickyHeader.classList.toggle('search-expanded');
      measureHeights();
      updateStickyState();
      
      // Focus the search input when expanding
      if (stickyHeader.classList.contains('search-expanded')) {
        const searchInput = stickyHeader.querySelector('#word-search');
        if (searchInput) {
          setTimeout(function() {
            searchInput.focus();
          }, 50);
        }
      }
    });

    // Close search when clicking outside (on desktop when stuck)
    document.addEventListener('click', function(e) {
      if (!stickyHeader.classList.contains('is-stuck') || !stickyHeader.classList.contains('search-expanded')) {
        return;
      }
      
      const wordSearchSection = stickyHeader.querySelector('.word-search-section');
      if (wordSearchSection && !wordSearchSection.contains(e.target) && !searchToggleBtn.contains(e.target)) {
        stickyHeader.classList.remove('search-expanded');
        measureHeights();
        updateStickyState();
      }
    });
  }

  // Initialize
  createSpacer();
  setupSearchToggle();
  
  // Wait a tick for CSS to apply before measuring
  requestAnimationFrame(function() {
    measureHeights();
    updateStickyState();
  });

  window.addEventListener('scroll', onScroll, { passive: true });
  window.addEventListener('resize', onResize, { passive: true });
})();
</script>
