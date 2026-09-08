'use strict';
const {test} = require('node:test');
const assert = require('node:assert/strict');
const {serverBootstrapEvidence, progressEvidence, diagnosticError} = require('../../tools/diagnostics/m32-local-acceptance.cjs');

test('server restoration verifies the real inline session bootstrap without requiring GET /state', () => {
    const item = {uuid: 'synthetic-private-id', chosen: ['PRIVATE-ANSWER'], manualInputsBySlot: [null], done: true, activeSlot: 0};
    const saved = {answered: 1, correct: 1, activeCardIdx: 0, items: [item]};
    const html = `<script>window.JS_TEST_PERSISTENCE = {\n token: 'PRIVATE-CSRF',\n saved: ${JSON.stringify(saved)},\n};</script>`;
    const evidence = serverBootstrapEvidence(html);
    assert.deepEqual(evidence, progressEvidence({answered: 1, correct: 1, activeCardIdx: 0,
        order: [item.uuid], items: [{chosen: item.chosen, manual: [''], done: true, activeSlot: 0}]}));
    assert.doesNotMatch(JSON.stringify(evidence), /PRIVATE|synthetic|token|chosen|manual/);
});

test('missing, null or malformed bootstrap fails closed without leaking source contents', () => {
    for (const value of ['', '<script>window.JS_TEST_PERSISTENCE = {\n saved: null,\n};</script>',
        '<script>window.JS_TEST_PERSISTENCE = {\n saved: PRIVATE-BROKEN-JSON,\n};</script>']) {
        assert.throws(() => serverBootstrapEvidence(value), error => !/PRIVATE/.test(error.message));
    }
});

test('Playwright timeout element previews and custom error names cannot leak private fields', () => {
    const error = new Error('Timeout: resolved <button data-opt="PRIVATE-ANSWER">PRIVATE-CSRF</button>');
    error.name = 'TimeoutError';
    const result = diagnosticError(error);
    assert.equal(result.type, 'TimeoutError');
    assert.match(result.messageSha256, /^[a-f0-9]{64}$/);
    assert.doesNotMatch(JSON.stringify(result), /PRIVATE|button|data-opt/);
    error.name = 'PRIVATE-NAME';
    assert.equal(diagnosticError(error).type, 'DiagnosticError');
});

test('malformed server counters cannot pass private strings through numeric evidence fields', () => {
    for (const field of ['answered', 'correct', 'activeCardIdx']) {
        for (const value of ['PRIVATE-COUNTER', -1, 0.5, Number.MAX_SAFE_INTEGER + 1]) {
            const saved = {answered: 1, correct: 1, activeCardIdx: 0, items: [], [field]: value};
            const html = `window.JS_TEST_PERSISTENCE = {\n saved: ${JSON.stringify(saved)},\n};`;
            assert.throws(() => serverBootstrapEvidence(html), error => !/PRIVATE/.test(error.message));
        }
    }
});
