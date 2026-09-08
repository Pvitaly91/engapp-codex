'use strict';

const test = require('node:test');
const assert = require('node:assert/strict');
const {assessGeometry, rectDifference, stableSidebarFootprint, sourceContract, options} = require('../../tools/diagnostics/theory-sidebar-stability.cjs');

const frame = (timestamp, overrides = {}) => ({
    timestamp, scrollY: 0, sidebar: {x: 60, y: 200, width: 410, height: 888, layoutWidth: 410, layoutHeight: 888},
    toc: {x: 60, y: 1112}, main: {x: 494, y: 200, width: 892},
    followingMenu: {x: 16, y: 900}, visibleStates: {loading: true, count: 1},
    state: {loading: true, open: true}, ...overrides,
});

test('sidebar probe accepts bounded local diagnostic options', () => {
    assert.equal(options(['sample', 'accept', '--observe-ms=9000', '--viewport=mobile']).observeMs, 9000);
    assert.equal(options(['sample', 'reproduce', '--scenario=saved-collapsed-delayed-alpine']).scenario, 'saved-collapsed-delayed-alpine');
    assert.equal(options(['sample', 'accept', '--desktop-retry-required']).desktopRetryRequired, true);
    for (const argv of [['../overwrite'], ['sample', 'bad-mode'], ['sample', 'accept', '--observe-ms=1200'],
        ['sample', 'accept', '--page=https://gramlyze.com'], ['sample', 'accept', '--scenario=unknown']]) {
        assert.throws(() => options(argv));
    }
});

test('rect deltas retain sidebar growth and never turn absent geometry into zero movement', () => {
    assert.deepEqual(rectDifference({height: 180}, {height: 888}, ['height']), {height: 708});
    assert.equal(rectDifference(null, {height: 888}, ['height']), null);
    const start = frame(110, {sidebar: {x: 60, y: 200, width: 410, height: 180}, toc: {x: 60, y: 404}});
    const end = frame(8000, {visibleStates: {loading: false, loaded: true, count: 1}});
    const result = assessGeometry({frames: [start, end], endState: end, paints: [{name: 'first-contentful-paint', timestamp: 100}]}, false);
    assert.equal(result.sidebarDelta.height, 708);
    assert.equal(result.tocDelta.y, 708);
});

test('pre-paint geometry and overlap are retained as evidence but do not count as a painted shift', () => {
    const early = frame(50, {sidebar: {x: 60, y: 200, width: 410, height: 180}, visibleStates: {loading: true, count: 2}});
    const painted = frame(120);
    const end = frame(8000);
    const result = assessGeometry({frames: [early, painted, end], endState: end, paints: [{name: 'first-contentful-paint', timestamp: 100}]}, false);
    assert.equal(result.earliest, early);
    assert.equal(result.initial, painted);
    assert.equal(result.overlappingStateFrames, 0);
    assert.equal(result.sidebarDelta.height, 0);
});

test('mobile geometry normalizes document position after click-driven scroll', () => {
    const start = frame(110, {scrollY: 0, sidebar: {x: 16, y: 400, width: 358, height: 716}});
    const end = frame(8000, {scrollY: 100, sidebar: {x: 16, y: 300, width: 358, height: 716},
        toc: {x: 60, y: 1012}, main: {x: 494, y: 100, width: 892}, followingMenu: {x: 16, y: 800}});
    const result = assessGeometry({frames: [start, end], endState: end, paints: []}, true);
    assert.equal(result.sidebarDelta.y, 0);
    assert.equal(result.followingMenuDelta.y, 0);
});

test('loading phase absent from first paint remains explicit while painted final geometry is usable', () => {
    const loaded = frame(120, {visibleStates: {loading: false, loaded: true, count: 1}});
    const result = assessGeometry({frames: [loaded], endState: loaded, paints: []}, false);
    assert.equal(result.initialLoading, undefined);
    assert.equal(result.initial, loaded);
    assert.equal(result.sidebarDelta.height, 0);
});

test('intentional mobile scale transition does not change the allocated sidebar footprint', () => {
    const opening = frame(120, {sidebar: {x: 24.95, y: 417.9, width: 340.1, height: 680.2, layoutWidth: 358, layoutHeight: 716}});
    const settled = frame(8000, {sidebar: {x: 16, y: 400, width: 358, height: 716, layoutWidth: 358, layoutHeight: 716}});
    const result = assessGeometry({frames: [opening, settled], endState: settled, paints: []}, true);
    assert.ok(result.sidebarDelta.width > 1, 'raw visual movement remains available as evidence');
    assert.ok(result.sidebarDelta.height > 1);
    assert.deepEqual(result.sidebarFootprintDelta, {layoutWidth: 0, layoutHeight: 0});
    assert.equal(stableSidebarFootprint(result), true);
    assert.equal(result.followingMenuDelta.y, 0);
});

test('real mobile allocation growth still fails even while a scale transition is present', () => {
    const loading = frame(120, {sidebar: {x: 24.95, y: 404.5, width: 340.1, height: 171, layoutWidth: 358, layoutHeight: 180},
        followingMenu: {x: 16, y: 604}});
    const loaded = frame(8000, {sidebar: {x: 16, y: 400, width: 358, height: 716, layoutWidth: 358, layoutHeight: 716},
        followingMenu: {x: 16, y: 1140}});
    const result = assessGeometry({frames: [loading, loaded], endState: loaded, paints: []}, true);
    assert.equal(result.sidebarFootprintDelta.layoutHeight, 536);
    assert.equal(result.followingMenuDelta.y, 536);
    assert.equal(stableSidebarFootprint(result), false);
    assert.equal(stableSidebarFootprint({sidebarFootprintDelta: null}), false);
    assert.equal(stableSidebarFootprint({sidebarFootprintDelta: {layoutWidth: NaN}}), false);
});

test('a cloaked TOC appearing below the fold is compared from its first actual layout box', () => {
    const hidden = frame(120, {toc: {x: 0, y: 0, width: 0, height: 0, display: 'none'}});
    const shown = frame(180, {toc: {x: 60, y: 1112, width: 410, height: 450}});
    const end = frame(8000, {toc: {x: 60, y: 1112, width: 410, height: 450}});
    const result = assessGeometry({frames: [hidden, shown, end], endState: end, paints: []}, false);
    assert.equal(result.initial, hidden, 'raw initial sidebar state is not discarded');
    assert.equal(result.tocInitial, shown);
    assert.deepEqual(result.tocDelta, {x: 0, y: 0});
    const shifted = frame(8000, {toc: {x: 60, y: 1829.5, width: 410, height: 450}});
    assert.equal(assessGeometry({frames: [hidden, shown, shifted], endState: shifted, paints: []}, false).tocDelta.y, 717.5);
});

test('raw response markers distinguish current templates without persisting private HTML', () => {
    assert.equal(sourceContract('<div x-show="error"></div>').cloakedErrorBlocks, 0);
    const contract = sourceContract('<div x-show="error" x-cloak role="alert"></div><div x-ref="content" x-show="!loading && !error" x-cloak></div>');
    assert.equal(contract.cloakedErrorBlocks, 1);
    assert.equal(contract.gatedContentBlocks, 1);
    assert.equal(contract.earlyCollapsedMarker, false);
});
