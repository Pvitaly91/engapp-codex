'use strict';
const assert = require('node:assert/strict');
const test = require('node:test');
const tool = require('../../tools/diagnostics/seo-m4-browser.cjs');

test('fixed four-page plan has eight independent viewport scenarios and no production navigation', () => {
    assert.equal(tool.PAGES.length * 2, 8);
    for (const [, pagePath] of tool.PAGES) assert.equal(tool.navigationUrl(pagePath), tool.BASE + pagePath);
    for (const pagePath of ['https://gramlyze.com/', '//gramlyze.com/', '/admin', '/courses?q=private', '/courses#x']) {
        assert.throws(() => tool.navigationUrl(pagePath));
    }
});

test('pre-navigation policy blocks production/subdomains/ub, redirects, credentials and retired runtime CDNs', () => {
    for (const url of ['https://gramlyze.com/test/x', 'http://sub.gramlyze.com/', 'https://gramlyze.ub/', 'https://gramlyze.com./', 'https://sub.gramlyze.ub./',
        'http://a.gramlyze.ub/', 'https://cdn.tailwindcss.com/', 'https://unpkg.com/alpinejs@3.0.0/dist/cdn.js',
        'https://unpkg.com/@alpinejs/collapse/dist/cdn.js', 'http://private:secret@gramlyze.loc/courses']) assert.ok(tool.requestDecision(url));
    assert.equal(tool.requestDecision(tool.BASE + '/courses', {navigation: true, redirected: true}), 'document-redirect');
    for (const url of ['https://example.org/', tool.BASE + '/admin', tool.BASE + '/courses?x=1']) assert.ok(tool.requestDecision(url, {navigation: true}));
    assert.equal(tool.requestDecision(tool.BASE + '/courses', {navigation: true}), null);
    assert.equal(tool.requestDecision('https://fonts.gstatic.com/font.woff2'), null);
});

test('actual Questions data endpoint retains the meaningful double questions segment', () => {
    const page = '/test/future-perfect/questions';
    assert.equal(tool.questionEndpoint(tool.BASE + page + '/questions', 'saved-test-js-v2', page), tool.BASE + page + '/questions?mode=saved-test-js-v2');
    const forms = '/test/future-perfect/forms';
    assert.equal(tool.questionEndpoint(forms + '/questions', 'saved-test-js-v2', forms), tool.BASE + forms + '/questions?mode=saved-test-js-v2');
    for (const endpoint of ['https://gramlyze.com' + page + '/questions', '//gramlyze.com/x', page + '/state',
        page + '/questions?token=PRIVATE', page + '/questions#PRIVATE', 'http://private:secret@gramlyze.loc' + page + '/questions']) {
        assert.throws(() => tool.questionEndpoint(endpoint, 'saved-test-js-v2', page));
    }
    assert.throws(() => tool.questionEndpoint(page + '/questions', 'PRIVATE-MODE', page));
});

test('question and diagnostic evidence cannot include answer payloads, CSRF, arbitrary names or URL credentials', () => {
    const evidence = tool.questionEvidence({questions: [{uuid: 'PRIVATE-ID', answers: ['PRIVATE-ANSWER'], question: 'PRIVATE-QUESTION'}], token: 'PRIVATE-CSRF'});
    assert.deepEqual(evidence, {count: 1});
    assert.throws(() => tool.questionEvidence({questions: {answer: 'PRIVATE'}}));
    const counters = {initial: 84, rendered: 84, cards: 84, answered: 0, enabledInputs: 84};
    assert.deepEqual(tool.questionUiEvidence({...counters, token: 'PRIVATE'}), counters);
    for (const answered of ['PRIVATE', {}, -1, Infinity, null]) assert.throws(() => tool.questionUiEvidence({...counters, answered}));
    const failure = tool.diagnosticError({name: 'PRIVATE-NAME', message: 'PRIVATE-ANSWER PRIVATE-CSRF'});
    assert.equal(failure.type, 'DiagnosticError');
    assert.match(failure.messageSha256, /^[a-f0-9]{64}$/);
    assert.ok(!JSON.stringify({evidence, failure}).includes('PRIVATE'));
    assert.equal(tool.safeUrl('https://private:secret@gramlyze.com/test/path?csrf=PRIVATE#PRIVATE'), 'https://gramlyze.com/test/path');
});

test('only exact same-route POST state204 abort pairs are expected, never other failures', () => {
    const page = '/test/future-perfect/questions';
    const state = tool.BASE + page + '/state';
    const record = {responses: [{url: state, status: 204, method: 'POST', type: 'fetch'}],
        failures: [{url: state, method: 'POST', error: 'net::ERR_ABORTED'}], console: [], blocked: []};
    assert.deepEqual(tool.networkEvidence(record, page), {expectedState204Aborts: 1, unexpectedFailures: 0, unsuccessfulResponses: 0, scriptErrors: 0, pass: true});
    for (const failure of [{url: state, method: 'GET', error: 'net::ERR_ABORTED'}, {url: state, method: 'POST', error: 'request-failed'},
        {url: tool.BASE + '/other/state', method: 'POST', error: 'net::ERR_ABORTED'}]) {
        assert.equal(tool.networkEvidence({...record, failures: [failure]}, page).pass, false);
    }
    assert.equal(tool.networkEvidence({...record, failures: [...record.failures, ...record.failures]}, page).pass, false);
    assert.equal(tool.networkEvidence({...record, responses: []}, page).pass, false);
    assert.equal(tool.networkEvidence({...record, blocked: [{reason: 'forbidden-host'}]}, page).pass, false);
    for (const entry of [{type: 'pageerror'}, {type: 'error'}, {type: 'warning', duplicateRuntime: true}]) {
        assert.equal(tool.networkEvidence({...record, console: [entry]}, page).pass, false);
    }
    assert.equal(tool.networkEvidence({...record, responses: [...record.responses, {type: 'fetch', status: 500}]}, page).pass, false);
    assert.equal(tool.networkEvidence({...record, responses: [...record.responses, {type: 'document', status: 302}]}, page).pass, false);
});

test('fresh context guard is installed before page creation and diagnostics are sanitized on collection', async () => {
    const events = [], handlers = {};
    const page = {on: (name, handler) => { handlers[name] = handler; }};
    const context = {route: async () => events.push('guard'), addInitScript: async () => events.push('init'),
        newPage: async () => { events.push('page'); return page; }};
    const options = [];
    const browser = {newContext: async option => { options.push(option); return context; }};
    const record = {console: [], responses: [], failures: [], blocked: []};
    await tool.prepare(browser, true, record);
    assert.deepEqual(events, ['guard', 'init', 'page']);
    assert.deepEqual(options[0].viewport, {width: 390, height: 844});
    assert.equal(options[0].serviceWorkers, 'block');
    assert.equal(options[0].storageState, undefined);
    handlers.console({type: () => 'error', text: () => 'PRIVATE-CSRF PRIVATE-ANSWER'});
    handlers.pageerror(new Error('PRIVATE-CSRF PRIVATE-ANSWER'));
    assert.equal(record.console[1].type, 'pageerror');
    assert.ok(!JSON.stringify(record).includes('PRIVATE'));
    assert.equal(tool.networkEvidence(record, '/courses').pass, false);
});

test('a strict network failure retains the public viewport screenshot without turning the result into a pass', async () => {
    const events = [], row = {network: {pass: false}};
    const screenshotPath = require('node:path').join(__dirname, 'm4-mocked-never-written-screenshot.png');
    const page = {screenshot: async options => {
        events.push('screenshot');
        assert.equal(options.path, screenshotPath);
        assert.equal(options.fullPage, false);
    }};
    await assert.rejects(() => tool.screenshotBeforeNetworkCheck(page, screenshotPath, row, (name, pass) => {
        events.push('network-assertion');
        assert.equal(name, 'no-unexpected-network-or-script-failures');
        assert.equal(pass, false);
        assert.ok(pass);
    }), {name: 'AssertionError'});
    assert.deepEqual(events, ['screenshot', 'network-assertion']);
    assert.equal(row.screenshot, 'm4-mocked-never-written-screenshot.png');
    assert.equal(row.network.pass, false);
});
