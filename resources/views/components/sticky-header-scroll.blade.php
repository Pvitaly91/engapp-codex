<script>
/**
 * Sticky header scroll detection - adds 'is-stuck' class when header is sticky
 * Only active on desktop (viewport width >= 1024px)
 * Includes layout shift prevention via dynamic spacer.
 * On public test pages with a dedicated host inside the site header,
 * the real controls node is moved into the main header instead of
 * rendering as a second sticky block below it.
 */
(function() {
  const stickyHeader = document.getElementById('sticky-header');
  if (!stickyHeader) return;
  const siteHeader = document.getElementById('site-header') || document.querySelector('body > .relative > header');
  const siteHeaderInner = siteHeader ? siteHeader.querySelector('div') : null;
  const siteHeaderControlsHost = document.getElementById('site-header-test-controls');
  const catalogShell = document.getElementById('catalog-shell');
  const stickyInner = stickyHeader.querySelector('.sticky-inner');
  const attachHysteresisPx = 48;
  const detachHysteresisPx = 48;

  // Desktop media query (min-width: 1024px)
  const desktopQuery = window.matchMedia('(min-width: 1024px)');

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

  function createSpacer() {
    const spacer = document.createElement('div');
    spacer.className = 'sticky-header-spacer';
    spacer.style.cssText = 'height: 0; pointer-events: none;';
    stickyHeader.parentNode.insertBefore(spacer, stickyHeader.nextSibling);

    return spacer;
  }

  if (siteHeader && siteHeaderControlsHost && stickyInner) {
    let ticking = false;
    let resizeTimeout = null;
    let stickyTriggerY = 0;
    let originalHeight = 0;
    let controlsAttached = false;
    const spacer = createSpacer();

    function measureHeaderBaseHeight() {
      if (!desktopQuery.matches || !siteHeader) {
        return 0;
      }

      const hostHeight = controlsAttached
        ? Math.round(siteHeaderControlsHost.getBoundingClientRect().height)
        : 0;

      return Math.max(0, Math.round(siteHeader.getBoundingClientRect().height) - hostHeight);
    }

    function measureTrigger(force) {
      if (!desktopQuery.matches) {
        stickyTriggerY = 0;
        return;
      }

      if (controlsAttached && !force) {
        return;
      }

      if (!controlsAttached) {
        originalHeight = Math.max(0, Math.round(stickyHeader.getBoundingClientRect().height));
      }

      const siteHeaderBaseHeight = measureHeaderBaseHeight();
      stickyTriggerY = Math.max(
        0,
        Math.round(stickyHeader.getBoundingClientRect().top + window.scrollY - siteHeaderBaseHeight)
      );
    }

    function attachControls() {
      if (controlsAttached || !desktopQuery.matches) {
        return;
      }

      originalHeight = Math.max(0, Math.round(stickyHeader.getBoundingClientRect().height)) || originalHeight;
      stickyInner.classList.add('header-attached');
      siteHeaderControlsHost.appendChild(stickyInner);
      stickyHeader.classList.add('is-stuck', 'is-attached');
      stickyHeader.classList.remove('search-expanded');
      spacer.style.height = originalHeight + 'px';
      controlsAttached = true;
      syncAttachedTestControls(true);
    }

    function detachControls() {
      if (!controlsAttached) {
        return;
      }

      stickyInner.classList.remove('header-attached');
      stickyHeader.appendChild(stickyInner);
      stickyHeader.classList.remove('is-stuck', 'is-attached', 'search-expanded');
      spacer.style.height = '0';
      controlsAttached = false;
      syncAttachedTestControls(false);
      originalHeight = Math.max(0, Math.round(stickyHeader.getBoundingClientRect().height));
      measureTrigger(true);
    }

    function updateAttachedState() {
      if (!desktopQuery.matches) {
        detachControls();
        ticking = false;
        return;
      }

      const shouldAttach = controlsAttached
        ? stickyTriggerY > 0 && window.scrollY >= Math.max(0, stickyTriggerY - detachHysteresisPx)
        : stickyTriggerY > 0 && window.scrollY >= stickyTriggerY + attachHysteresisPx;

      if (shouldAttach) {
        attachControls();
      } else {
        detachControls();
      }

      ticking = false;
    }

    function onScroll() {
      if (!ticking) {
        window.requestAnimationFrame(updateAttachedState);
        ticking = true;
      }
    }

    function onResize() {
      clearTimeout(resizeTimeout);
      resizeTimeout = setTimeout(function() {
        measureTrigger(!controlsAttached);
        updateAttachedState();
      }, 100);
    }

    requestAnimationFrame(function() {
      measureTrigger(true);
      updateAttachedState();
    });

    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onResize, { passive: true });

    if (typeof ResizeObserver === 'function') {
      const resizeObserver = new ResizeObserver(function() {
        if (controlsAttached) {
          updateAttachedState();
          return;
        }

        originalHeight = Math.max(0, Math.round(stickyHeader.getBoundingClientRect().height));
        measureTrigger(true);
        updateAttachedState();
      });

      resizeObserver.observe(stickyHeader);
      resizeObserver.observe(siteHeader);
      resizeObserver.observe(siteHeaderControlsHost);
    }

    return;
  }

  let ticking = false;
  let originalHeight = 0;
  let stuckHeight = 0;
  let stickyTriggerY = 0;
  let spacer = null;

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

  function measureHeights() {
    if (!desktopQuery.matches) {
      stickyTriggerY = 0;
      return;
    }

    syncStickyOffset();

    const wasStuck = stickyHeader.classList.contains('is-stuck');
    const wasExpanded = stickyHeader.classList.contains('search-expanded');
    stickyHeader.classList.remove('is-stuck', 'search-expanded');
    syncAttachedTestControls(false);

    stickyTriggerY = Math.max(
      0,
      Math.round(stickyHeader.getBoundingClientRect().top + window.scrollY - getSiteHeaderOffset())
    );

    originalHeight = stickyHeader.offsetHeight;

    stickyHeader.classList.add('is-stuck');
    stuckHeight = stickyHeader.offsetHeight;

    if (!wasStuck) {
      stickyHeader.classList.remove('is-stuck');
    }
    if (wasExpanded) {
      stickyHeader.classList.add('search-expanded');
    }

    syncAttachedTestControls(wasStuck);

    stickyHeader.style.setProperty('--sticky-header-original-height', originalHeight + 'px');
    stickyHeader.style.setProperty('--sticky-header-stuck-height', stuckHeight + 'px');
    stickyHeader.style.setProperty('--sticky-header-trigger-y', stickyTriggerY + 'px');
  }

  function updateStickyState() {
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
      stickyHeader.classList.remove('search-expanded');
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

  let resizeTimeout = null;
  function onResize() {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(function() {
      syncStickyOffset();
      measureHeights();
      updateStickyState();
    }, 100);
  }

  function setupSearchToggle() {
    const searchToggleBtn = document.getElementById('sticky-search-toggle');
    if (!searchToggleBtn) return;

    searchToggleBtn.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();

      stickyHeader.classList.toggle('search-expanded');
      measureHeights();
      updateStickyState();

      if (stickyHeader.classList.contains('search-expanded')) {
        const searchInput = stickyHeader.querySelector('#word-search');
        if (searchInput) {
          setTimeout(function() {
            searchInput.focus();
          }, 50);
        }
      }
    });

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

  spacer = createSpacer();
  setupSearchToggle();

  requestAnimationFrame(function() {
    measureHeights();
    updateStickyState();
  });

  window.addEventListener('scroll', onScroll, { passive: true });
  window.addEventListener('resize', onResize, { passive: true });
})();
</script>
