import {afterEach, describe, expect, it, vi} from 'vitest';
import {JSDOM} from 'jsdom';
import {initTheoryNavigation, tocPinOffset} from '../../resources/js/theory-navigation.js';

const fixtures = [];
afterEach(() => {
    for (const fixture of fixtures.splice(0)) {
        fixture.cleanup?.();
        fixture.window.close();
    }
});

function fixture({root = true, reduced = false, visible = true} = {}) {
    const dom = new JSDOM(`<header id="site-header"></header><div data-theory-layout>
        <aside data-theory-aside><div data-theory-sidebar-legacy>
            <section data-theory-sidebar><h2>Topics</h2><a href="/theory/present-perfect">Present Perfect</a></section>
            ${root ? '<div data-theory-toc-pin-root><section data-theory-toc-card><div data-theory-toc-links><a id="toc-link" href="#target">Authored heading</a><a id="missing-link" href="#missing">Missing</a></div></section></div>' : ''}
        </div></aside><main class="theory-design" data-theory-main><h2 id="target">Authored heading</h2></main>
        </div>`, {url: 'http://gramlyze.loc/theory/sidebar-fixture', pretendToBeVisual: true});
    const {window} = dom, {document} = window;
    const state = {headerBottom: 100, rootTop: 80, asideBottom: 1800, cardHeight: 260, reduced, visible};
    Object.defineProperty(window, 'innerHeight', {value: 900, writable: true});
    Object.defineProperty(window, 'innerWidth', {value: 1440, writable: true});
    Object.defineProperty(window, 'scrollY', {value: 200, writable: true});
    const rect = (top, height = 100) => ({top, bottom: top + height, left: 40, right: 340, width: 300, height});
    document.querySelector('#site-header').getBoundingClientRect = () => rect(state.headerBottom - 60, 60);
    const aside = document.querySelector('[data-theory-aside]');
    aside.getBoundingClientRect = () => rect(0, state.asideBottom);
    const pin = document.querySelector('[data-theory-toc-pin-root]');
    const card = document.querySelector('[data-theory-toc-card]');
    if (pin) {
        pin.getBoundingClientRect = () => rect(state.rootTop, state.cardHeight);
        pin.getClientRects = () => state.visible ? [rect(state.rootTop, state.cardHeight)] : [];
        Object.defineProperty(pin, 'offsetParent', {get: () => state.visible ? aside : null});
        card.getBoundingClientRect = () => rect(state.rootTop, state.cardHeight);
        Object.defineProperty(card, 'offsetHeight', {get: () => state.cardHeight});
    }
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
    const delays = new Map(); let delayId = 0;
    window.setTimeout = vi.fn((callback, delay) => {delays.set(++delayId, {callback, delay}); return delayId;});
    window.clearTimeout = vi.fn(id => delays.delete(id));
    const cleanup = initTheoryNavigation(document, window);
    const value = {window, document, cleanup, state, pin, card, frames, observers, delays, flush};
    fixtures.push(value);
    flush();
    return value;
}

describe('legacy contents offset', () => {
    it.each([
        [100, {gap: 16, top: 116}],
        [0, {gap: 16, top: 16}],
        [-60, {gap: 16, top: 16}],
        [73.4, {gap: 16, top: 89}],
        [73.5, {gap: 16, top: 90}],
    ])('uses the visible header bottom and the legacy sixteen-pixel gap (%s)', (bottom, expected) => {
        expect(tocPinOffset(bottom)).toEqual(expected);
    });
});

describe('legacy independent contents pinning', () => {
    it('keeps the full menu and learning DOM without sizing a shared sidebar shell', () => {
        const f = fixture(), menu = f.document.querySelector('[data-theory-sidebar]');
        const main = f.document.querySelector('[data-theory-main]');
        const beforeMenu = menu.outerHTML, beforeMain = main.outerHTML;
        f.window.dispatchEvent(new f.window.Event('scroll')); f.flush();
        expect(menu.outerHTML).toBe(beforeMenu);
        expect(main.outerHTML).toBe(beforeMain);
        expect(f.document.querySelector('[data-theory-sidebar-shell]')).toBeNull();
        const style = f.document.querySelector('[data-theory-layout]').style;
        expect(style.getPropertyValue('--theory-nav-top')).toBe('');
        expect(style.getPropertyValue('--theory-sidebar-height')).toBe('');
    });
    it('pins only the contents card after its natural position reaches the header gap', () => {
        const f = fixture();
        expect(f.card.style.position).toBe('fixed');
        expect(f.card.style.top).toBe('116px');
        expect(f.card.style.left).toBe('40px');
        expect(f.card.style.width).toBe('300px');
        expect(f.card.style.maxHeight).toBe('calc(100vh - 132px)');
        expect(f.card.style.overflowY).toBe('auto');
        expect(f.pin.style.minHeight).toBe('260px');
    });
    it('keeps the card in document flow before the pin threshold', () => {
        const f = fixture();
        f.state.rootTop = 240;
        f.window.dispatchEvent(new f.window.Event('scroll')); f.flush();
        expect(f.card.style.position).toBe('');
        expect(f.pin.style.minHeight).toBe('');
        expect(f.card.style.maxHeight).toBe('');
    });
    it('stops the pinned card at the aside bottom instead of covering later content', () => {
        const f = fixture();
        f.state.asideBottom = 350;
        f.window.dispatchEvent(new f.window.Event('scroll')); f.flush();
        expect(f.card.style.position).toBe('absolute');
        expect(f.card.style.top).toBe('');
        expect(f.card.style.bottom).toBe('0px');
        expect(f.card.style.left).toBe('0px');
    });
    it.each(['mobile', 'hidden'])('resets pin styles when the desktop contents is unavailable (%s)', reason => {
        const f = fixture();
        if (reason === 'mobile') f.window.innerWidth = 800;
        else f.state.visible = false;
        f.window.dispatchEvent(new f.window.Event('resize')); f.flush();
        expect(f.card.getAttribute('style') || '').not.toMatch(/fixed|absolute|132px|260px/);
        expect(f.pin.style.minHeight).toBe('');
    });
    it('observes header resize without assuming a fixed header height', () => {
        const f = fixture(), header = f.document.querySelector('#site-header');
        expect(f.observers.some(observer => observer.targets.includes(header))).toBe(true);
        f.state.headerBottom = 280;
        for (const observer of f.observers) observer.callback([{target: header}]);
        f.flush();
        expect(f.card.style.top).toBe('296px');
    });
    it.each([false, true])('supports rich fragment anchors, history and reduced motion (reduced=%s)', reduced => {
        const f = fixture({reduced}), before = f.window.history.length;
        const event = new f.window.MouseEvent('click', {bubbles: true, cancelable: true});
        f.document.querySelector('#toc-link').dispatchEvent(event); f.flush();
        expect(event.defaultPrevented).toBe(true);
        expect(f.window.location.hash).toBe('#target');
        expect(f.window.history.length).toBe(before + 1);
        expect(f.window.scrollTo).toHaveBeenCalledWith({top: 634, behavior: reduced ? 'auto' : 'smooth'});
    });
    it('preserves the current mobile/content TOC offset rather than applying the legacy desktop gap', () => {
        const f = fixture();
        const navigation = f.document.createElement('nav');
        navigation.setAttribute('data-theory-toc-links', '');
        const link = f.document.createElement('a');
        link.href = '#target'; link.textContent = 'Mobile contents'; navigation.append(link);
        const main = f.document.querySelector('[data-theory-main]');
        main.append(navigation);
        const before = main.outerHTML;
        const event = new f.window.MouseEvent('click', {bubbles: true, cancelable: true});
        link.dispatchEvent(event); f.flush();
        expect(event.defaultPrevented).toBe(true);
        expect(f.window.scrollTo).toHaveBeenCalledWith({top: 678, behavior: 'smooth'});
        expect(main.outerHTML).toBe(before);
    });
    it('retains the deferred pin pass after smooth scrolling and cancels it on cleanup', () => {
        const f = fixture();
        f.document.querySelector('#toc-link').dispatchEvent(new f.window.MouseEvent('click', {bubbles: true, cancelable: true}));
        expect(f.delays.size).toBe(1);
        expect([...f.delays.values()][0].delay).toBe(450);
        f.cleanup(); f.cleanup = null;
        expect(f.delays.size).toBe(0);
        expect(f.frames.size).toBe(0);
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
    it('removes its handlers, pending frames, pin styles and observers on cleanup', () => {
        const f = fixture();
        f.window.dispatchEvent(new f.window.Event('resize'));
        expect(typeof f.cleanup).toBe('function');
        f.cleanup(); f.cleanup = null;
        expect(f.observers.every(observer => observer.disconnected)).toBe(true);
        expect(f.pin.style.minHeight).toBe('');
        expect(f.card.style.position).toBe('');
        f.window.dispatchEvent(new f.window.Event('scroll')); f.flush();
        expect(f.card.style.position).toBe('');
        const event = new f.window.MouseEvent('click', {bubbles: true, cancelable: true});
        f.document.querySelector('#toc-link').dispatchEvent(event);
        expect(event.defaultPrevented).toBe(false);
        expect(f.window.scrollTo).not.toHaveBeenCalled();
    });
    it('does not assign pinning styles on pages without lesson contents', () => {
        const f = fixture({root: false});
        f.window.dispatchEvent(new f.window.Event('resize')); f.flush();
        expect(f.document.querySelector('[data-theory-sidebar]').getAttribute('style')).toBeNull();
        expect(f.window.scrollTo).not.toHaveBeenCalled();
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
