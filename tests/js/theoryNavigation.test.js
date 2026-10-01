import {afterEach, describe, expect, it, vi} from 'vitest';
import {JSDOM} from 'jsdom';
import {initTheoryNavigation, sidebarMetrics} from '../../resources/js/theory-navigation.js';

const fixtures = [];
afterEach(() => {
    for (const fixture of fixtures.splice(0)) {
        fixture.cleanup?.();
        fixture.window.close();
    }
});

function fixture({layout = true, reduced = false, visible = true} = {}) {
    const dom = new JSDOM(`<header id="site-header"></header><div ${layout ? 'data-theory-layout' : ''}>
        <aside data-theory-aside><div data-theory-sidebar-shell>
            <section data-theory-sidebar-card="topics"><h2>Topics</h2><div data-theory-sidebar-panel="topics">
                <a href="/theory/present-perfect">Present Perfect</a>
            </div></section>
            <section data-theory-sidebar-card="lesson"><h2>Contents</h2><div data-theory-sidebar-panel="lesson"><nav data-theory-toc-links>
            <a id="toc-link" href="#target">Authored heading</a><a id="missing-link" href="#missing">Missing</a>
        </nav></div></section></div></aside><main data-theory-main><h2 id="target">Authored heading</h2></main>
        </div>`, {url: 'http://gramlyze.loc/theory/sidebar-fixture', pretendToBeVisual: true});
    const {window} = dom, {document} = window;
    const state = {headerHeight: 100, sidebarTop: 240, reduced};
    Object.defineProperty(window, 'innerHeight', {value: 900, writable: true});
    Object.defineProperty(window, 'innerWidth', {value: 1440, writable: true});
    Object.defineProperty(window, 'scrollY', {value: 200, writable: true});
    const rect = (top, height = 100) => ({top, bottom: top + height, left: 0, right: 300, width: 300, height});
    document.querySelector('#site-header').getBoundingClientRect = () => rect(0, state.headerHeight);
    Object.defineProperty(document.querySelector('#site-header'), 'offsetHeight', {get: () => state.headerHeight});
    for (const element of document.querySelectorAll('[data-theory-layout], [data-theory-aside], [data-theory-sidebar-shell]')) {
        element.getBoundingClientRect = () => rect(state.sidebarTop, 500);
    }
    document.querySelector('[data-theory-sidebar-shell]').getClientRects = () => visible ? [rect(state.sidebarTop, 500)] : [];
    document.querySelector('#target').getBoundingClientRect = () => rect(550, 60);
    const frames = new Map(); let frameId = 0;
    window.requestAnimationFrame = vi.fn(callback => {frames.set(++frameId, callback); return frameId;});
    window.cancelAnimationFrame = vi.fn(id => frames.delete(id));
    const flush = () => {
        for (let limit = 0; frames.size && limit < 20; limit++) {
            const batch = [...frames]; frames.clear();
            for (const [, callback] of batch) callback(0);
        }
        expect(frames.size).toBe(0);
    };
    const observers = [];
    window.ResizeObserver = class {
        constructor(callback) {this.callback = callback; this.targets = []; this.disconnected = false; observers.push(this);}
        observe(target) {this.targets.push(target);}
        disconnect() {this.disconnected = true;}
    };
    window.matchMedia = vi.fn(query => ({matches: query.includes('prefers-reduced-motion') ? state.reduced : true}));
    window.scrollTo = vi.fn();
    const cleanup = initTheoryNavigation(document, window);
    const value = {window, document, cleanup, state, frames, observers, flush};
    fixtures.push(value);
    flush();
    return value;
}

describe('viewport-bound theory sidebar metrics', () => {
    it.each([
        [900, 100, 260, {top: 112, height: 628}],
        [900, 100, 20, {top: 112, height: 776}],
        [650, 100, 240, {top: 112, height: 398}],
        [160, 150, 10, {top: 162, height: 0}],
        [900, 0, 100, {top: 16, height: 788}],
        [650.9, 73.4, 125.7, {top: 86, height: 513}],
        [900, 100, -100, {top: 112, height: 776}],
        [0, 0, 0, {top: 16, height: 0}],
    ])('uses available viewport, including boundary/fractional geometry (%s,%s,%s)', (height, header, sidebarTop, expected) => {
        expect(sidebarMetrics(height, header, sidebarTop)).toEqual(expected);
    });
});

describe('progressive theory navigation enhancement', () => {
    it('keeps both sibling cards and their full navigation DOM without tab or visibility mutation', () => {
        const f = fixture(), shell = f.document.querySelector('[data-theory-sidebar-shell]');
        const before = shell.innerHTML;
        f.window.innerHeight = 650;
        f.window.dispatchEvent(new f.window.Event('resize'));
        f.window.dispatchEvent(new f.window.Event('scroll')); f.flush();
        expect(shell.innerHTML).toBe(before);
        expect([...shell.children].map(card => card.dataset.theorySidebarCard)).toEqual(['topics', 'lesson']);
        expect(shell.querySelectorAll('[role="tablist"], [role="tab"], [role="tabpanel"], [hidden]')).toHaveLength(0);
        expect(shell.querySelector('[data-theory-sidebar-panel="topics"]').textContent).toContain('Present Perfect');
        expect(shell.querySelector('[data-theory-sidebar-panel="lesson"]').textContent).toContain('Authored heading');
    });
    it('updates CSS geometry on initial layout, short viewport resize and scroll', () => {
        const f = fixture(), style = f.document.querySelector('[data-theory-layout]').style;
        expect(style.getPropertyValue('--theory-nav-top')).toBe('112px');
        expect(style.getPropertyValue('--theory-sidebar-height')).toBe('648px');
        f.window.innerHeight = 650;
        f.window.dispatchEvent(new f.window.Event('resize')); f.flush();
        expect(style.getPropertyValue('--theory-sidebar-height')).toBe('398px');
        f.state.sidebarTop = -100;
        f.window.dispatchEvent(new f.window.Event('scroll')); f.flush();
        expect(style.getPropertyValue('--theory-sidebar-height')).toBe('526px');
    });
    it('observes header resize without assuming a fixed header height', () => {
        const f = fixture(), header = f.document.querySelector('#site-header');
        expect(f.observers.some(observer => observer.targets.includes(header))).toBe(true);
        f.state.headerHeight = 280;
        for (const observer of f.observers) observer.callback([{target: header}]);
        f.flush();
        const style = f.document.querySelector('[data-theory-layout]').style;
        expect(style.getPropertyValue('--theory-nav-top')).toBe('292px');
        expect(style.getPropertyValue('--theory-sidebar-height')).toBe('596px');
    });
    it.each([false, true])('preserves fragment history and reduced-motion scrolling (reduced=%s)', reduced => {
        const f = fixture({reduced}), before = f.window.history.length;
        const event = new f.window.MouseEvent('click', {bubbles: true, cancelable: true});
        f.document.querySelector('#toc-link').dispatchEvent(event); f.flush();
        expect(event.defaultPrevented).toBe(true);
        expect(f.window.location.hash).toBe('#target');
        expect(f.window.history.length).toBe(before + 1);
        expect(f.window.scrollTo).toHaveBeenCalled();
        const options = f.window.scrollTo.mock.calls.at(-1)[0];
        expect(options.behavior).toBe(reduced ? 'auto' : 'smooth');
        expect(options.top).toBeGreaterThanOrEqual(0);
        expect(options.top).toBeLessThanOrEqual(750);
    });
    it('does not intercept modified anchor navigation or invent a missing target', () => {
        const f = fixture();
        const modified = new f.window.MouseEvent('click', {bubbles: true, cancelable: true, ctrlKey: true});
        f.document.querySelector('#toc-link').dispatchEvent(modified); f.flush();
        expect(modified.defaultPrevented).toBe(false);
        expect(f.window.scrollTo).not.toHaveBeenCalled();
        const missing = new f.window.MouseEvent('click', {bubbles: true, cancelable: true});
        f.document.querySelector('#missing-link').dispatchEvent(missing); f.flush();
        expect(missing.defaultPrevented).toBe(false);
        expect(f.window.scrollTo).not.toHaveBeenCalled();
    });
    it('removes its handlers, pending frames and observers on cleanup', () => {
        const f = fixture(), style = f.document.querySelector('[data-theory-layout]').style;
        f.window.dispatchEvent(new f.window.Event('resize'));
        expect(typeof f.cleanup).toBe('function');
        f.cleanup(); f.cleanup = null;
        expect(f.observers.every(observer => observer.disconnected)).toBe(true);
        const before = style.getPropertyValue('--theory-sidebar-height');
        f.window.innerHeight = 650;
        f.window.dispatchEvent(new f.window.Event('resize')); f.flush();
        expect(style.getPropertyValue('--theory-sidebar-height')).toBe(before);
        const event = new f.window.MouseEvent('click', {bubbles: true, cancelable: true});
        f.document.querySelector('#toc-link').dispatchEvent(event);
        expect(event.defaultPrevented).toBe(false);
        expect(f.window.scrollTo).not.toHaveBeenCalled();
    });
    it('does not alter pages outside the theory layout scope', () => {
        const f = fixture({layout: false});
        f.window.dispatchEvent(new f.window.Event('resize')); f.flush();
        expect(f.frames.size).toBe(0);
        expect(f.document.querySelector('div').style.getPropertyValue('--theory-nav-top')).toBe('');
        expect(f.window.scrollTo).not.toHaveBeenCalled();
    });
    it('does not assign desktop sidebar geometry when the shell is hidden on mobile', () => {
        const f = fixture({visible: false});
        f.window.dispatchEvent(new f.window.Event('resize')); f.flush();
        const style = f.document.querySelector('[data-theory-layout]').style;
        expect(style.getPropertyValue('--theory-nav-top')).toBe('');
        expect(style.getPropertyValue('--theory-sidebar-height')).toBe('');
    });
    it('coalesces repeated viewport events into one pending animation frame', () => {
        const f = fixture();
        for (let index = 0; index < 5; index++) {
            f.window.dispatchEvent(new f.window.Event('scroll'));
            f.window.dispatchEvent(new f.window.Event('resize'));
        }
        expect(f.frames.size).toBe(1);
        f.flush();
    });
});
