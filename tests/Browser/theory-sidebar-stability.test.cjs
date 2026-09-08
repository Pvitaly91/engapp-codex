'use strict';

const test = require('node:test');
const assert = require('node:assert/strict');
const {assessGeometry, assessStateVisibility, exclusiveVisibleStates, rectDifference, stableSidebarFootprint, sourceContract, responseIdentity, options} = require('../../tools/diagnostics/theory-sidebar-stability.cjs');

const frame = (timestamp, overrides = {}) => ({
    label: 'geometry-change', timestamp, scrollY: 0, sidebar: {x: 60, y: 200, width: 410, height: 888, layoutWidth: 410, layoutHeight: 888},
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

test('state-source capture tolerates attribute order, quoting and encoded expressions', () => {
    const contract = sourceContract(`<div x-cloak role='alert' x-show='error'></div>
        <div style="display: none;" x-cloak x-show="!loading &amp;&amp; !error" x-ref=content></div>`);
    assert.equal(contract.cloakedErrorBlocks, 1);
    assert.equal(contract.gatedContentBlocks, 1);
    assert.equal(contract.blocks[0].xShow, 'error');
    assert.equal(contract.blocks[0].role, 'alert');
    assert.equal(contract.blocks[1].inlineDisplay, 'none');
    assert.match(contract.htmlSha256, /^[a-f0-9]{64}$/);
});

test('HTML identity changes with private bytes, while sanitized state markup contains no private content', () => {
    const html = token => `<meta name="csrf-token" content="${token}"><div x-data="{secret:'${token}'}">
        <div x-show="error" x-cloak>${token}</div><input value="${token}"></div>`;
    const first = sourceContract(html('PRIVATE-ONE')), second = sourceContract(html('PRIVATE-TWO'));
    assert.notEqual(first.htmlSha256, second.htmlSha256);
    assert.equal(first.stateMarkupSha256, second.stateMarkupSha256);
    assert.equal(JSON.stringify(first).includes('PRIVATE'), false);
    const unknown = sourceContract('<div x-ref="content" x-show="privateSecretValue"></div>');
    assert.equal(unknown.blocks[0].xShow, '[unrecognized-expression]');
    assert.equal(JSON.stringify(unknown).includes('privateSecretValue'), false);
});

test('response identity hashes exact bytes and strips URL query without retaining HTML', async () => {
    const body = Buffer.from('<div x-show="loading">Завантаження</div>');
    const response = {body: async () => body, status: () => 200,
        url: () => 'http://gramlyze.loc/theory/basic-grammar/sentence-types?private=value'};
    const result = await responseIdentity(response, 'document');
    assert.equal(result.bytes, body.length);
    assert.equal(result.url, 'http://gramlyze.loc/theory/basic-grammar/sentence-types');
    assert.equal(result.contract.blocks[0].kind, 'loading');
    assert.equal(JSON.stringify(result).includes('Завантаження'), false);
    assert.equal(result.sha256, result.contract.htmlSha256);
    const css = await responseIdentity(response, 'stylesheet');
    assert.equal(css.contract, undefined);
    await assert.rejects(responseIdentity({...response, body: async () => { throw new Error('body unavailable'); }}, 'document'));
});

test('M3.2 repetition stays bounded and requires an explicitly scoped new-guest scenario', () => {
    const config = options(['m32-first', 'accept', '--stage=m3-2', '--runs=10', '--page=sentence-types', '--viewport=desktop', '--scenario=new-guest']);
    assert.equal(config.runs, 10);
    assert.equal(config.stage, 'm3-2');
    assert.equal(options(['m32-alpine', 'accept', '--scenario=delayed-alpine']).scenario, 'delayed-alpine');
    for (const flags of [['--runs=11'], ['--runs=0'], ['--stage=../../escape'], ['--runs=10'],
        ['--runs=10', '--page=sentence-types', '--viewport=desktop', '--scenario=delay']]) {
        assert.throws(() => options(['sample', 'accept', ...flags]));
    }
});

test('post-FCP mutation overlap is retained but is not mislabeled a rendered animation frame', () => {
    const loading = frame(110);
    const betweenCallbacks = frame(120, {label: 'state-attribute-mutation', capturePhase: 'mutation',
        visibleStates: {loading: true, error: false, loaded: true, count: 2},
        content: {x: 78, y: 353.5, width: 368, height: 717.5}});
    const loaded = frame(140, {visibleStates: {loading: false, loaded: true, count: 1}});
    const trace = {frames: [loading, betweenCallbacks, loaded], shifts: [], paints: [{name: 'first-contentful-paint', timestamp: 100}]};
    const raw = JSON.stringify(trace), assessment = assessStateVisibility(trace);
    assert.equal(assessment.rawPostFcpOverlapSnapshots, 1);
    assert.equal(assessment.mutationOverlapSnapshots, 1);
    assert.equal(assessment.overlappingStateFrames, 0);
    assert.equal(exclusiveVisibleStates(assessment), true);
    assert.equal(JSON.stringify(trace), raw, 'every original mutation observation remains intact');
});

test('real rAF loading/error overlap fails, including legacy geometry-change labels', () => {
    for (const capturePhase of [undefined, 'animation-frame']) {
        const overlap = frame(120, {capturePhase, visibleStates: {loading: true, error: true, count: 2}});
        const assessment = assessStateVisibility({frames: [overlap], shifts: [], paints: [{name: 'first-contentful-paint', timestamp: 100}]});
        assert.equal(assessment.overlappingStateFrames, 1);
        assert.equal(exclusiveVisibleStates(assessment), false);
    }
});

test('actual 84px content LayoutShift still fails when only a mutation snapshot caught the overlap', () => {
    const previous = {x: 78, y: 353.5, width: 368, height: 717.5}, current = {x: 78, y: 269.5, width: 368, height: 801.5};
    for (const hadRecentInput of [false, true]) {
        const trace = {frames: [frame(120, {label: 'state-attribute-mutation', visibleStates: {loading: true, loaded: true, count: 2}, content: previous})],
            paints: [{name: 'first-contentful-paint', timestamp: 100}],
            shifts: [{timestamp: 140, value: 0.0108824, hadRecentInput, sources: [{previous, current}]}]};
        const assessment = assessStateVisibility(trace);
        assert.equal(assessment.overlappingStateFrames, 0);
        assert.equal(assessment.corroboratingContentShifts.length, 1);
        assert.equal(exclusiveVisibleStates(assessment), false, 'physical shifts cannot be hidden behind recent-input or capture phase');
        trace.shifts[0].sources[0] = {previous: {x: 921, y: 41, width: 457}, current: {x: 845, y: 41, width: 533}};
        assert.equal(exclusiveVisibleStates(assessStateVisibility(trace)), true, 'unrelated header movement is not sidebar evidence');
    }
});

test('unknown capture phases fail closed rather than silently dropping unexplained overlap', () => {
    for (const change of [{label: 'unrecognized'}, {capturePhase: 'unrecognized'}]) {
        const assessment = assessStateVisibility({frames: [frame(120, {...change, visibleStates: {count: 2}})], paints: [], shifts: []});
        assert.equal(assessment.unknownOverlapSnapshots, 1);
        assert.equal(exclusiveVisibleStates(assessment), false);
    }
});
