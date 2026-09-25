'use strict';
const {test} = require('node:test');
const assert = require('node:assert/strict');
const {JSDOM} = require('jsdom');
const {installObserver, measureColdNavigation} = require('../../tools/diagnostics/public-layout-shifts.cjs');
const {schedule} = require('../../tools/diagnostics/mobile-performance-m32.cjs');

test('fixed paired schedule includes exactly10 pairs perpage, balanced AB/BA and interleaved pages', () => {
    const rows = schedule();
    assert.equal(rows.length, 20); assert.equal(new Set(rows.map(r => r.pairId)).size, 20);
    for (const name of ['sentence-types', 'present-perfect']) {
        const group = rows.filter(r => r.name === name);
        assert.equal(group.length, 10); assert.equal(group.filter(r => r.order === 'AB').length, 5);
        assert.equal(group.filter(r => r.order === 'BA').length, 5);
    }
    for (let i = 1; i < rows.length; i++) assert.notEqual(rows[i].name, rows[i - 1].name);
});
test('performance helper rejects production and arbitrary loopback hostnames before browser creation', async () => {
    for (const base of ['https://gramlyze.com', 'https://gramlyze.ub', 'http://example.com', 'http://localhost:8123']) {
        await assert.rejects(measureColdNavigation(null, {base, name: 'sentence-types', url: '/theory/basic-grammar/sentence-types'}));
    }
});
test('input diagnostic records eventkind/trust but no key, input value, answers or page HTML', () => {
    const dom = new JSDOM('<input value="PRIVATE-ANSWER"><main>PRIVATE-LESSON</main>', {url: 'http://gramlyze.loc', runScripts: 'outside-only'});
    const w = dom.window;
    w.PerformanceObserver = class {static supportedEntryTypes = ['layout-shift', 'largest-contentful-paint', 'longtask']; observe() {} disconnect() {} takeRecords() {return [];} };
    w.ResizeObserver = class {observe() {} disconnect() {}};
    w.requestAnimationFrame = () => 1;
    w.visualViewport = null;
    Object.defineProperty(w.document, 'fonts', {value: new w.EventTarget()});
    w.eval(`(${installObserver.toString()})({windowMs:12000,sidebarExpected:false,diagnosticTimeline:true})`);
    w.document.querySelector('input').dispatchEvent(new w.KeyboardEvent('keydown', {key: 'PRIVATE-KEY', bubbles: true}));
    w.dispatchEvent(new w.MouseEvent('click'));
    w.dispatchEvent(new w.Event('resize'));
    const events = w.__m31.events;
    assert.ok(events.some(e => e.type === 'keydown' && e.isTrusted === false && e.targetTag === 'INPUT'));
    assert.ok(events.some(e => e.type === 'window-resize' && e.viewport.width > 0));
    assert.doesNotMatch(JSON.stringify(w.__m31), /PRIVATE-|outerHTML|innerHTML|cookie|csrf/i);
    assert.ok(w.__m31.observerCost.installMs >= 0);
    dom.window.close();
});
