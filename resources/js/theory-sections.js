/** Progressive enhancement only: every permitted section is already server DOM. */
export function initTheorySections(document, window) {
    const scopes = [...document.querySelectorAll('[data-theory-design], [data-theory-main], [data-theory-section]')];
    if (!scopes.length) return () => {};

    const owns = (element) => scopes.some((scope) => scope === element || scope.contains(element));
    const revealFragment = () => {
        let fragment;
        try {
            fragment = decodeURIComponent(window.location.hash.slice(1));
        } catch {
            return;
        }
        if (!fragment) return;
        const target = document.getElementById(fragment);
        if (!target || !owns(target)) return;
        const closed = [];
        for (let node = target.parentElement; node; node = node.parentElement) {
            if (node.tagName === 'DETAILS' && !node.open) closed.push(node);
        }
        for (const details of closed.reverse()) details.open = true;
        if (closed.length) {
            // Only a real fragment navigation may scroll. No initial UI layout jump.
            window.requestAnimationFrame(() => target.scrollIntoView({ block: 'start', behavior: 'auto' }));
        }
    };
    let printStates = null;
    const beforePrint = () => {
        if (printStates !== null) return;
        printStates = [...document.querySelectorAll('[data-theory-details]')].map((details) => [details, details.open]);
        for (const [details] of printStates) details.open = true;
    };
    const afterPrint = () => {
        if (printStates === null) return;
        for (const [details, open] of printStates) {
            if (details.isConnected) details.open = open;
        }
        printStates = null;
    };
    const printMedia = typeof window.matchMedia === 'function' ? window.matchMedia('print') : null;
    const onPrintChange = (event) => event.matches ? beforePrint() : afterPrint();
    window.addEventListener('hashchange', revealFragment);
    window.addEventListener('popstate', revealFragment);
    window.addEventListener('beforeprint', beforePrint);
    window.addEventListener('afterprint', afterPrint);
    printMedia?.addEventListener?.('change', onPrintChange);
    revealFragment();

    return () => {
        window.removeEventListener('hashchange', revealFragment);
        window.removeEventListener('popstate', revealFragment);
        window.removeEventListener('beforeprint', beforePrint);
        window.removeEventListener('afterprint', afterPrint);
        printMedia?.removeEventListener?.('change', onPrintChange);
        afterPrint();
    };
}
