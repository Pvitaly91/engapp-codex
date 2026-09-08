const {test} = require('node:test');
const assert = require('node:assert/strict');
const {createClsSessionWindow, summarizeShifts} = require('../../tools/diagnostics/cls-session-window.cjs');
const {assessMeasurement, distribution} = require('../../tools/diagnostics/public-layout-shifts.cjs');
const shift = (startTime, value = 0.125, hadRecentInput = false) => ({startTime, value, hadRecentInput});

test('empty navigation reports both metrics as zero', () => {
    assert.deepEqual(summarizeShifts([]), {clsSessionWindow: 0, legacyShiftSum: 0, windows: [], worstWindow: null});
});
test('CLS selects the maximum session, separately from the legacy sum', () => {
    const result = summarizeShifts([shift(100), shift(200), shift(1300, 0.5), shift(2400)]);
    assert.equal(result.clsSessionWindow, 0.5);
    assert.equal(result.legacyShiftSum, 0.875);
    assert.equal(result.windows.length, 3);
    assert.deepEqual(result.worstWindow, {start: 1300, end: 1300, value: 0.5, entries: 1});
});
test('a gap below 1000 ms belongs to the same session', () => {
    assert.equal(summarizeShifts([shift(100), shift(1099.999)]).clsSessionWindow, 0.25);
});
test('a gap exactly 1000 ms starts a new session', () => {
    assert.equal(summarizeShifts([shift(100), shift(1100)]).clsSessionWindow, 0.125);
});
test('a gap above 1000 ms starts a new session', () => {
    assert.equal(summarizeShifts([shift(100), shift(1100.001)]).windows.length, 2);
});
test('session age below 5000 ms is included when every gap is below 1000 ms', () => {
    const result = summarizeShifts([0, 900, 1800, 2700, 3600, 4500, 4999.999].map(t => shift(t)));
    assert.equal(result.windows.length, 1);
    assert.equal(result.clsSessionWindow, 0.875);
});
test('session age exactly 5000 ms starts a new window even with a short final gap', () => {
    const result = summarizeShifts([0, 900, 1800, 2700, 3600, 4500, 5000].map(t => shift(t)));
    assert.equal(result.windows.length, 2);
    assert.equal(result.clsSessionWindow, 0.75);
    assert.equal(result.legacyShiftSum, 0.875);
});
test('session age above 5000 ms starts a new window, not a sliding tail window', () => {
    const result = summarizeShifts([0, 900, 1800, 2700, 3600, 4500, 5000.001, 5500].map(t => shift(t)));
    assert.deepEqual(result.windows.map(w => w.entries), [6, 2]);
});
test('recent-input entries contribute to neither metric and cannot bridge a gap', () => {
    const result = summarizeShifts([shift(0), shift(900, 2, true), shift(1100)]);
    assert.equal(result.clsSessionWindow, 0.125);
    assert.equal(result.legacyShiftSum, 0.25);
    assert.equal(result.windows.length, 2);
});
test('recent-input entries do not reset an existing session', () => {
    assert.equal(summarizeShifts([shift(0), shift(500, 2, true), shift(900)]).clsSessionWindow, 0.25);
});
test('zero-value entries match the reference session-value guard', () => {
    const result = summarizeShifts([shift(0, 0), shift(900), shift(1800)]);
    assert.equal(result.worstWindow.start, 900);
    assert.equal(result.clsSessionWindow, 0.25);
});
test('navigation reset clears maximum, sum, windows and timestamp epoch', () => {
    const metric = createClsSessionWindow();
    metric.add(shift(9000, 0.75));
    metric.reset();
    metric.add(shift(10));
    assert.equal(metric.snapshot().clsSessionWindow, 0.125);
    assert.equal(metric.snapshot().legacyShiftSum, 0.125);
    assert.equal(metric.snapshot().windows.length, 1);
});
test('independent navigations and snapshot callers cannot share mutable state', () => {
    const first = createClsSessionWindow(), second = createClsSessionWindow();
    first.add(shift(20));
    const snapshot = first.snapshot();
    snapshot.windows[0].value = 8;
    assert.equal(first.snapshot().windows[0].value, 0.125);
    assert.equal(second.snapshot().clsSessionWindow, 0);
});
test('invalid or out-of-order raw input fails instead of silently understating CLS', () => {
    assert.throws(() => summarizeShifts([shift(100), shift(99)]), /chronological/);
    for (const entry of [shift(-1), shift(0, -1), shift(NaN), shift(0, Infinity)]) {
        assert.throws(() => summarizeShifts([entry]), TypeError);
    }
});

const completeTrace = () => ({
    complete: true, requestedEnd: 12000, observationEndedAt: 12001,
    timings: {load: 1500, fontsReady: 1800, alpineInitialized: 1700, sidebarReady: 4000, alpineInit: [1600]},
    unsupported: [], visibility: [{timestamp: 0, state: 'visible'}], iframeCount: 0,
    fontStatus: 'loaded', fcp: 1400, lcpEntries: [{timestamp: 1900}],
    shifts: [{timestamp: 4000, value: 0.125, hadRecentInput: false}],
});
test('complete fixed-window measurements retain standard CLS and legacy sum', () => {
    assert.equal(assessMeasurement(completeTrace()).status, 'complete');
    assert.equal(assessMeasurement(completeTrace()).clsSessionWindow, 0.125);
});
test('timeout or a missing trace cannot pass as low CLS', () => {
    assert.equal(assessMeasurement(null).status, 'incomplete');
    const trace = completeTrace(); trace.complete = false;
    assert.ok(assessMeasurement(trace).reasons.includes('observation-timeout'));
});
test('every readiness milestone requires at least two seconds before cutoff', () => {
    for (const key of ['load', 'fontsReady', 'alpineInitialized', 'sidebarReady']) {
        const trace = completeTrace(); trace.timings[key] = 10000;
        assert.equal(assessMeasurement(trace).status, 'complete');
        trace.timings[key] = 10000.001;
        assert.equal(assessMeasurement(trace).status, 'incomplete');
        trace.timings[key] = null;
        assert.equal(assessMeasurement(trace).status, 'incomplete');
    }
});
test('all raw shifts survive assessment, including recent input and timer overrun', () => {
    const trace = completeTrace();
    trace.shifts.push({timestamp: 4500, value: 4, hadRecentInput: true}, {timestamp: 12000.001, value: 8, hadRecentInput: false});
    const result = assessMeasurement(trace);
    assert.equal(trace.shifts.length, 3);
    assert.equal(result.clsSessionWindow, 0.125);
    assert.equal(result.rawEntryCount, 3);
    assert.equal(result.metricEntryCount, 2);
    assert.equal(result.afterCutoffEntryCount, 1);
});
test('hidden documents, missing API/paint, duplicate Alpine and late timers are incomplete', () => {
    for (const change of [
        trace => trace.visibility.push({timestamp: 10, state: 'hidden'}),
        trace => trace.unsupported.push('layout-shift'),
        trace => trace.timings.alpineInit.push(1900),
        trace => { trace.observationEndedAt = 12251; },
        trace => { trace.fcp = null; },
        trace => { trace.lcpEntries = []; },
        trace => { trace.iframeCount = 1; },
    ]) {
        const trace = completeTrace(); change(trace);
        assert.equal(assessMeasurement(trace).status, 'incomplete');
    }
});
test('HTTP errors, blocked requests and page errors never receive complete status', () => {
    for (const options of [{status: 500}, {blocked: ['http://gramlyze.com/']}, {pageErrors: 1}]) {
        assert.equal(assessMeasurement(completeTrace(), options).status, 'incomplete');
    }
});
test('summary distribution preserves worst result and computes odd/even medians', () => {
    assert.deepEqual(distribution([9, 1, 5, 3, 2]), {median: 3, min: 1, max: 9});
    assert.deepEqual(distribution([1, 9]), {median: 5, min: 1, max: 9});
    assert.equal(distribution([]), null);
});
