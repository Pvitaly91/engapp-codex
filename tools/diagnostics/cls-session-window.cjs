// Lab-only reducer, independently implemented against web-vitals v6.2.1:
// https://github.com/GoogleChrome/web-vitals/blob/v6.2.1/src/lib/LayoutShiftManager.ts
// https://github.com/GoogleChrome/web-vitals/blob/v6.2.1/src/onCLS.ts
// Strict <1000 ms gap / <5000 ms session age, NOT a lifetime shift sum.
'use strict';

function createClsSessionWindow() {
    let state;
    const reset = () => {
        state = {clsSessionWindow: 0, legacyShiftSum: 0, windows: [], worstWindow: null};
    };
    reset();
    let lastTimestamp = -Infinity;
    return {
        add(entry) {
            const timestamp = entry.startTime ?? entry.timestamp;
            if (!Number.isFinite(timestamp) || timestamp < 0 || timestamp < lastTimestamp
                || !Number.isFinite(entry.value) || entry.value < 0) {
                throw new TypeError('Layout shifts must have nonnegative values and chronological timestamps');
            }
            lastTimestamp = timestamp;
            if (entry.hadRecentInput) return;
            state.legacyShiftSum += entry.value;
            let session = state.windows.at(-1);
            if (!session?.value || timestamp - session.end >= 1000 || timestamp - session.start >= 5000) {
                session = {start: timestamp, end: timestamp, value: 0, entries: 0};
                state.windows.push(session);
            }
            session.end = timestamp;
            session.value += entry.value;
            session.entries++;
            if (session.value > state.clsSessionWindow) {
                state.clsSessionWindow = session.value;
                state.worstWindow = {...session};
            }
        },
        reset() { reset(); lastTimestamp = -Infinity; },
        snapshot() { return JSON.parse(JSON.stringify(state)); },
    };
}

function summarizeShifts(entries) {
    const metric = createClsSessionWindow();
    for (const entry of entries) metric.add(entry);
    return metric.snapshot();
}

module.exports = {createClsSessionWindow, summarizeShifts};
