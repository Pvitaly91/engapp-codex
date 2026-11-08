/** Legacy separate sidebar cards; current lesson presentation is not mutated. */
export function tocPinOffset(headerBottom) {
    const gap = 16;
    return {gap, top: Math.max(gap, Math.round(headerBottom + gap))};
}

const resetPin = (root, card) => {
    root.style.minHeight = '';
    for (const key of ['position', 'top', 'left', 'width', 'maxHeight', 'overflowY', 'zIndex', 'bottom']) card.style[key] = '';
};

export function initTheoryNavigation(document, window) {
    const layouts = [...document.querySelectorAll('[data-theory-layout]')];
    if (!layouts.length) return () => {};
    const pins = layouts.flatMap(layout => [...layout.querySelectorAll('[data-theory-toc-pin-root]')])
        .map(root => ({root, card: root.querySelector('[data-theory-toc-card]')})).filter(pin => pin.card);
    const header = document.getElementById('site-header');
    const offset = () => tocPinOffset(header?.getBoundingClientRect().bottom || 0);
    let pending = null;
    let delayed = null;
    const update = () => {
        pending = null;
        for (const {root, card} of pins) {
            resetPin(root, card);
            if (window.innerWidth < 1024 || root.offsetParent === null) continue;
            const {gap, top} = offset();
            const rect = root.getBoundingClientRect();
            const aside = root.closest('[data-theory-aside]');
            const asideRect = aside?.getBoundingClientRect();
            const rootDocumentTop = window.scrollY + rect.top;
            if (window.scrollY < rootDocumentTop - top) continue;
            root.style.minHeight = card.offsetHeight + 'px';
            card.style.left = Math.round(rect.left) + 'px';
            card.style.width = Math.round(rect.width) + 'px';
            card.style.maxHeight = 'calc(100vh - ' + (top + gap) + 'px)';
            card.style.overflowY = 'auto';
            card.style.zIndex = '30';
            const asideDocumentBottom = asideRect ? window.scrollY + asideRect.bottom : Number.POSITIVE_INFINITY;
            if (window.scrollY + top + card.offsetHeight >= asideDocumentBottom - gap && aside) {
                card.style.position = 'absolute';
                card.style.bottom = '0';
                card.style.left = '0';
            } else {
                card.style.position = 'fixed';
                card.style.top = top + 'px';
            }
        }
    };
    const schedule = () => {
        if (pending === null) pending = window.requestAnimationFrame(update);
    };
    const onContentsClick = event => {
        if (event.defaultPrevented || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        const link = event.target.closest('[data-theory-toc-links] a[href^="#"]');
        if (!link || !layouts.some(layout => layout.contains(link))) return;
        let id;
        try { id = decodeURIComponent(link.hash.slice(1)); } catch { return; }
        const target = document.getElementById(id);
        if (!target) return;
        event.preventDefault();
        // Preserve the current in-content/mobile TOC offset; only the legacy
        // desktop card uses its original header-bottom + 16px pin position.
        const top = link.closest('[data-theory-toc-card]') ? offset().top
            : Math.max(16, Math.ceil((header?.getBoundingClientRect().height || 0) + 12));
        window.scrollTo({top: Math.max(0, window.scrollY + target.getBoundingClientRect().top - top),
            behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth'});
        window.history.pushState(null, '', link.hash);
        if (delayed !== null) window.clearTimeout(delayed);
        delayed = window.setTimeout(() => {delayed = null; schedule();}, 450);
    };
    const observer = typeof window.ResizeObserver === 'function' ? new window.ResizeObserver(schedule) : null;
    if (header) observer?.observe(header);
    window.addEventListener('scroll', schedule, {passive: true});
    window.addEventListener('resize', schedule);
    window.addEventListener('load', schedule);
    document.addEventListener('alpine:initialized', schedule);
    document.addEventListener('click', onContentsClick);
    schedule();
    return () => {
        if (pending !== null) window.cancelAnimationFrame(pending);
        if (delayed !== null) window.clearTimeout(delayed);
        observer?.disconnect();
        window.removeEventListener('scroll', schedule);
        window.removeEventListener('resize', schedule);
        window.removeEventListener('load', schedule);
        document.removeEventListener('alpine:initialized', schedule);
        document.removeEventListener('click', onContentsClick);
        for (const {root, card} of pins) resetPin(root, card);
    };
}
