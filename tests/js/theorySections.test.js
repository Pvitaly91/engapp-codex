import { describe, expect, it } from 'vitest';
import { JSDOM } from 'jsdom';
import { initTheorySections } from '../../resources/js/theory-sections.js';

function fixture(hash = '') {
    const dom = new JSDOM('<main data-theory-design><details data-theory-details><summary>One</summary><section id="first"><details id="key"><summary>Answers</summary><a id="deep" href="#second">Key</a></details></section></details><details data-theory-details open><summary>Two</summary><p id="second">Text</p></details></main><details id="other"><summary>Other</summary><p id="external">External</p></details>', { url: `http://gramlyze.loc/fixture${hash}`, pretendToBeVisual: true });
    dom.window.HTMLElement.prototype.scrollIntoView = function () { this.dataset.scrolled = 'yes'; };
    const mediaListeners = [];
    dom.window.matchMedia = () => ({ addEventListener: (_event, callback) => mediaListeners.push(callback), removeEventListener: (_event, callback) => mediaListeners.splice(mediaListeners.indexOf(callback), 1) });
    const cleanup = initTheorySections(dom.window.document, dom.window);
    return { window: dom.window, cleanup, mediaListeners };
}

describe('server-rendered theory disclosure enhancement', () => {
    it('does not collapse existing sections or keys when there is no fragment', () => {
        const { window } = fixture();
        const details = window.document.querySelectorAll('[data-theory-details]');
        expect([...details].map((node) => node.open)).toEqual([false, true]);
        expect(window.document.querySelector('#key').open).toBe(false);
    });
    it('opens all required ancestors without closing an independent topic', () => {
        const { window } = fixture('#deep');
        expect([...window.document.querySelectorAll('[data-theory-details]')].map((node) => node.open)).toEqual([true, true]);
        expect(window.document.querySelector('#key').open).toBe(true);
    });
    it('supports hashchange and Back/Forward popstate, while leaving unrelated disclosures alone', () => {
        const { window } = fixture();
        window.history.pushState({}, '', '#deep');
        window.dispatchEvent(new window.PopStateEvent('popstate'));
        expect(window.document.querySelector('#key').open).toBe(true);
        window.location.hash = '#external';
        window.dispatchEvent(new window.HashChangeEvent('hashchange'));
        expect(window.document.querySelector('#other').open).toBe(false);
    });
    it('does not crash for malformed fragments or a missing target', () => {
        const { window } = fixture('#%E0%A4%A');
        window.location.hash = '#not-present';
        expect(() => window.dispatchEvent(new window.HashChangeEvent('hashchange'))).not.toThrow();
    });
    it('opens all detail explanations for print and restores their exact state', () => {
        const { window, mediaListeners } = fixture();
        mediaListeners[0]({ matches: true });
        window.dispatchEvent(new window.Event('beforeprint'));
        expect([...window.document.querySelectorAll('[data-theory-details]')].map((node) => node.open)).toEqual([true, true]);
        expect(window.document.querySelector('#key').open).toBe(false);
        mediaListeners[0]({ matches: false });
        window.dispatchEvent(new window.Event('afterprint'));
        expect([...window.document.querySelectorAll('[data-theory-details]')].map((node) => node.open)).toEqual([false, true]);
    });
    it('cleanup restores a print state and removes listeners', () => {
        const { window, cleanup, mediaListeners } = fixture();
        window.dispatchEvent(new window.Event('beforeprint'));
        cleanup();
        expect(mediaListeners).toHaveLength(0);
        expect(window.document.querySelector('[data-theory-details]').open).toBe(false);
        window.history.pushState({}, '', '#deep');
        window.dispatchEvent(new window.PopStateEvent('popstate'));
        expect(window.document.querySelector('#key').open).toBe(false);
    });
});
