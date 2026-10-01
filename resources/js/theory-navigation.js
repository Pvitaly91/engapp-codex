/** One viewport-bound sidebar; no independent fixed cards or content mutation. */
export function sidebarMetrics(viewportHeight, headerHeight, sidebarTop) {
    const top = Math.max(16, Math.ceil(headerHeight + 12));
    const height = Math.max(0, Math.floor(viewportHeight - Math.max(top, sidebarTop) - 12));
    return { top, height };
}

export function initTheoryNavigation(document, window) {
    const layouts = [...document.querySelectorAll('[data-theory-layout]')];
    if (!layouts.length) return () => {};
    const header = document.getElementById('site-header');
    let pending = null;
    const update = () => {
        pending = null;
        for (const layout of layouts) {
            const shell = layout.querySelector('[data-theory-sidebar-shell]');
            if (!shell || !shell.getClientRects().length) continue;
            const { top, height } = sidebarMetrics(window.innerHeight,
                header?.getBoundingClientRect().height || 0, shell.getBoundingClientRect().top);
            layout.style.setProperty('--theory-nav-top', `${top}px`);
            layout.style.setProperty('--theory-sidebar-height', `${height}px`);
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
        const top = sidebarMetrics(window.innerHeight, header?.getBoundingClientRect().height || 0, 0).top;
        window.scrollTo({ top: Math.max(0, window.scrollY + target.getBoundingClientRect().top - top),
            behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
        window.history.pushState(null, '', link.hash);
    };
    const observer = typeof window.ResizeObserver === 'function' ? new window.ResizeObserver(schedule) : null;
    if (header) observer?.observe(header);
    window.addEventListener('scroll', schedule, { passive: true });
    window.addEventListener('resize', schedule);
    window.addEventListener('load', schedule);
    document.addEventListener('click', onContentsClick);
    schedule();
    return () => {
        if (pending !== null) window.cancelAnimationFrame(pending);
        observer?.disconnect();
        window.removeEventListener('scroll', schedule);
        window.removeEventListener('resize', schedule);
        window.removeEventListener('load', schedule);
        document.removeEventListener('click', onContentsClick);
    };
}
