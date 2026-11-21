// @vitest-environment node
import fs from 'node:fs';
import vm from 'node:vm';
import { JSDOM } from 'jsdom';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

const views = 'resources/views/';
const endpoint = '/api/word-search/uk';
const storageKey = 'gramlyze:js-test-state:v2:manual-autocomplete-fixture';
const fixtureUrl = 'http://gramlyze.loc/test/step-manual-autocomplete-fixture/step';
const contractionRules = JSON.parse(fs.readFileSync('public/data/english-contractions.json', 'utf8'));
const sessions = [];

function scripts(file) {
    return [...fs.readFileSync(views + file, 'utf8').matchAll(/<script>([\s\S]*?)<\/script>/g)].map(match => match[1]);
}

function deferred() {
    let resolve;
    let reject;
    const promise = new Promise((yes, no) => { resolve = yes; reject = no; });
    return { promise, resolve, reject };
}

function question(id, answers = ['english']) {
    const markers = answers.map((_, index) => `a${index + 1}`);
    return {
        id, uuid: `manual-autocomplete-${id}`, type: '0', level: 'A1',
        question: `Choose ${markers.map(marker => `{${marker}}`).join(' and ')}.`,
        answers, markers, markers_count: markers.length, options: answers,
        answer_map: Object.fromEntries(markers.map((marker, index) => [marker, answers[index]])),
    };
}

async function harness({ questions = [question(1), question(2)], saved = null } = {}) {
    const dom = new JSDOM(`<!doctype html><html lang="uk"><body>
        <button id="restart-test" type="button"></button><button id="outside" type="button"></button>
        <div id="ajax-loader" class="hidden"></div>
        <div id="question-wrap"><div id="question-card"></div><button id="prev"></button><button id="next"></button></div>
        <div id="summary"><span id="summary-text"></span><button id="retry"></button></div>
        <span id="progress-label"></span><span id="score-label"></span><span id="progress-bar"></span>
    </body></html>`, { url: fixtureUrl, runScripts: 'outside-only' });
    sessions.push(dom);
    const { window } = dom;
    const { document } = window;
    const ctx = dom.getInternalVMContext();
    const run = source => vm.runInContext(source, ctx);
    const requests = [];
    const reset = deferred();
    const resets = [];
    const error = vi.fn();
    const boundary = vi.fn();
    Object.assign(window, {
        setTimeout, clearTimeout, Date, AbortController,
        console: { ...console, error },
        __INITIAL_JS_TEST_QUESTIONS__: structuredClone(questions),
        __fixtureReload: boundary,
        JS_TEST_PERSISTENCE: { endpoint: null, mode: 'step-easy', storageKey, saved: null },
        GRAMLYZE_CONTRACTION_RULES: contractionRules,
        fetch: vi.fn((value, options) => {
            const url = new URL(value, fixtureUrl);
            if (url.pathname === endpoint) {
                const pending = deferred();
                requests.push({ query: url.searchParams.get('q'), url, options, start: Date.now(), ...pending });
                // Simulate a transport that can still complete after abort.
                return pending.promise;
            }
            if (url.pathname === '/fixture/state' && JSON.parse(options.body).state === null) {
                resets.push(options);
                return reset.promise;
            }
            throw new Error(`Unexpected request: ${url.pathname}`);
        }),
        scrollTo() {},
    });
    window.HTMLElement.prototype.scrollIntoView = () => {};
    if (saved !== null) window.localStorage.setItem(storageKey, saved);

    run(fs.readFileSync('public/js/english-answer-variants.js', 'utf8'));
    run(`let QUESTIONS = window.__INITIAL_JS_TEST_QUESTIONS__;
        const CSRF_TOKEN = 'fixture', TEST_LOCALE = 'uk', TEST_SLUG = 'fixture',
            EXPLAIN_URL = '/fixture/explain', MARKER_THEORY_URL = '/fixture/theory',
            WORD_SEARCH_URL = '${endpoint}', JS_IS_ADMIN = false,
            IS_POLYGLOT_STEP_PREVIEW = false, COMPOSE_TOKENS_QUESTION_TYPE = '4';`);
    // Use production code for the actual inline component, selection keyboard,
    // grading and persistence. Only Blade values and jsdom's navigation boundary
    // are substituted. The fixture never uses the real network or browser storage.
    run(scripts('components/saved-test-js-helpers.blade.php')[0]
        .replace(/^window\.[\w_]+ = .*@json\(.*$/gm, '')
        .replaceAll('{{ csrf_token() }}', 'fixture')
        .replace('window.location.reload();', 'window.__fixtureReload();'));
    run(scripts('components/sentence-reorder-question-js.blade.php')[0]);
    run(scripts('components/marker-theory-js.blade.php')[0].replace(/@json\([^\n]+\)(?=\);)/, 'false'));
    run(scripts('components/test-suggestion-keyboard.blade.php')[0]);
    run(scripts('test-modes/step-easy.blade.php')[1]
        .replace("{{ localized_route('question.hint') }}", '/fixture/hint')
        .replace(/^init\(\);$/m, 'window.__fixtureStartup = init();'));
    await window.__fixtureStartup;

    const get = id => document.getElementById(id);
    const input = () => document.querySelector('input[data-manual-active="true"]:not(:disabled)');
    const list = () => document.querySelector('[data-manual-suggestions]');
    return {
        window, document, run, get, input, list, requests, resets, reset, boundary, error,
        item: () => run('state.items[state.current]'),
        state: () => run('state'),
        stored: () => JSON.parse(window.localStorage.getItem(storageKey))?.state,
        storedRaw: () => window.localStorage.getItem(storageKey),
        suggestions: () => [...list()?.querySelectorAll('button[data-word-suggestion]') ?? []].map(element => element.dataset.wordSuggestion),
        type(value) {
            const field = input();
            expect(field).not.toBeNull();
            field.focus();
            field.value = value;
            field.dispatchEvent(new window.Event('input', { bubbles: true }));
            return field;
        },
        press(key, field = input()) {
            const event = new window.KeyboardEvent('keydown', { key, bubbles: true, cancelable: true });
            field.dispatchEvent(event);
            return event;
        },
    };
}

const tick = ms => vi.advanceTimersByTimeAsync(ms);
const hit = (word, translation = '', forms = null) => ({ word, translation, forms });
const terms = fixture => fixture.requests.map(request => request.query);

async function complete(request, body) {
    const json = vi.fn(async () => body);
    request.resolve({ ok: true, status: 200, json });
    await tick(0);
    return json;
}

function closed(fixture) {
    expect(fixture.suggestions()).toEqual([]);
    expect(fixture.list()?.classList.contains('hidden') ?? true).toBe(true);
    expect(fixture.input()?.getAttribute('aria-expanded')).not.toBe('true');
}

beforeEach(() => {
    vi.useFakeTimers({ toFake: ['setTimeout', 'clearTimeout', 'Date'] });
    vi.setSystemTime(new Date('2026-11-21T00:00:00Z'));
});
afterEach(() => {
    sessions.splice(0).forEach(dom => dom.window.close());
    vi.clearAllTimers();
    vi.useRealTimers();
});

describe('Step Easy manual word suggestions', () => {
    it('saves each keystroke immediately but fetches only the final word after 250 ms of quiet', async () => {
        const fixture = await harness();
        const initialStateFields = Object.keys(fixture.item()).sort();
        for (const value of ['en', 'eng', 'engl', 'engli', 'englis']) {
            fixture.type(value);
            expect(fixture.stored().items[0].manualInputsBySlot[0]).toBe(value);
            await tick(60);
        }
        fixture.type('english');
        const lastEdit = Date.now();
        expect(fixture.stored().items[0].manualInputsBySlot[0]).toBe('english');
        expect(Object.keys(fixture.stored().items[0]).sort()).toEqual(initialStateFields);
        expect(fixture.item().wordSearchRequestBySlot[0]).toBeGreaterThan(0);
        await tick(249);
        expect(terms(fixture)).toEqual([]);
        closed(fixture);
        await tick(1);
        expect(terms(fixture)).toEqual(['english']);
        expect(fixture.requests[0].start - lastEdit).toBe(250);
        expect(fixture.requests[0].options.headers.Accept).toBe('application/json');
        const json = await complete(fixture.requests[0], [hit('english', 'англійська', { adjective: ['English'] })]);
        expect(json).toHaveBeenCalledOnce();
        expect(fixture.suggestions()).toEqual(['english', 'English']);
        expect(fixture.list().textContent).toContain('англійська');
        expect(fixture.input().getAttribute('aria-expanded')).toBe('true');
        expect(fixture.error).not.toHaveBeenCalled();
    });

    it('makes independent requests for typing pauses and starts each new input without stale choices', async () => {
        const fixture = await harness();
        for (const value of ['en', 'engl', 'english']) {
            fixture.type(value);
            closed(fixture);
            await tick(310);
            await complete(fixture.requests.at(-1), [hit(value)]);
            expect(fixture.suggestions()).toEqual([value]);
        }
        expect(terms(fixture)).toEqual(['en', 'engl', 'english']);
        expect(fixture.requests.map(request => request.options.signal.aborted)).toEqual([false, false, false]);
    });

    it.each(['', ' x '])('cancels the pending search for a shortened draft %j and keeps the list closed', async shortened => {
        const fixture = await harness();
        fixture.type('en');
        await tick(80);
        fixture.type(shortened);
        await tick(600);
        expect(terms(fixture)).toEqual([]);
        closed(fixture);
        expect(fixture.error).not.toHaveBeenCalled();
    });

    it('aborts an in-flight fetch immediately and rejects its response before decoding', async () => {
        const fixture = await harness();
        fixture.type('eng');
        await tick(250);
        fixture.type('english');
        expect(fixture.requests[0].options.signal.aborted).toBe(true);
        const late = await complete(fixture.requests[0], [hit('stale')]);
        expect(late).not.toHaveBeenCalled();
        expect(terms(fixture)).toEqual(['eng']);
        closed(fixture);
        await tick(250);
        await complete(fixture.requests[1], [hit('english')]);
        expect(fixture.suggestions()).toEqual(['english']);
    });

    it('rechecks identity after JSON, even across the same text A → B → A', async () => {
        const fixture = await harness();
        const pendingJson = deferred();
        const json = vi.fn(() => pendingJson.promise);
        fixture.type('en');
        await tick(250);
        fixture.requests[0].resolve({ ok: true, json });
        await tick(0);
        expect(json).toHaveBeenCalledOnce();
        fixture.type('english');
        await tick(250);
        fixture.type('en');
        await tick(250);
        expect(terms(fixture)).toEqual(['en', 'english', 'en']);
        expect(fixture.requests.map(request => request.options.signal.aborted)).toEqual([true, true, false]);
        await complete(fixture.requests[2], [hit('current en')]);
        const late = await complete(fixture.requests[1], [hit('stale english')]);
        expect(late).not.toHaveBeenCalled();
        pendingJson.resolve([hit('original en')]);
        await tick(0);
        expect(fixture.suggestions()).toEqual(['current en']);
        expect(fixture.item().wordSuggestionsBySlot[0]).toEqual([hit('current en')]);
    });

    it('does not let stale JSON failure release the newer active controller', async () => {
        const fixture = await harness();
        const pendingJson = deferred();
        fixture.type('en');
        await tick(250);
        fixture.requests[0].resolve({ ok: true, json: () => pendingJson.promise });
        await tick(0);
        fixture.type('english');
        await tick(250);
        pendingJson.reject(new SyntaxError('Old response body'));
        await tick(0);
        fixture.press('Escape');
        expect(fixture.requests[1].options.signal.aborted).toBe(true);
        await complete(fixture.requests[1], [hit('too late after Escape')]);
        closed(fixture);
        expect(fixture.error).not.toHaveBeenCalled();
    });

    it('cannot apply a result after the live input changed without a new event', async () => {
        const fixture = await harness();
        const field = fixture.type('eng');
        await tick(250);
        field.value = 'new DOM value';
        await complete(fixture.requests[0], [hit('stale against actual input')]);
        closed(fixture);
        expect(fixture.item().wordSuggestionsBySlot[0]).toEqual([]);
        expect(field.value).toBe('new DOM value');
    });

    it.each(['Escape', 'blur'])('%s cancels both scheduled and active work, and a later search works again', async action => {
        const fixture = await harness();
        const dismiss = () => action === 'Escape' ? fixture.press('Escape') : fixture.get('outside').focus();
        fixture.type('en');
        await tick(80);
        dismiss();
        await tick(250);
        expect(terms(fixture)).toEqual([]);
        closed(fixture);

        fixture.type('eng');
        await tick(250);
        dismiss();
        expect(fixture.requests[0].options.signal.aborted).toBe(true);
        fixture.requests[0].reject(Object.assign(new Error('Canceled by the browser'), { name: 'AbortError' }));
        await tick(0);
        closed(fixture);
        expect(fixture.error).not.toHaveBeenCalled();

        fixture.type('english');
        await tick(250);
        await complete(fixture.requests[1], [hit('english')]);
        expect(fixture.suggestions()).toEqual(['english']);
        if (action === 'Escape') fixture.press('Escape');
        else fixture.get('outside').focus();
        closed(fixture);
    });

    it('clears cached-list markup and active work when a word becomes shorter than two characters', async () => {
        const fixture = await harness();
        fixture.type('en');
        await tick(250);
        await complete(fixture.requests[0], [hit('english')]);
        expect(fixture.suggestions()).toEqual(['english']);
        fixture.type('eng');
        await tick(250);
        fixture.type('e');
        expect(fixture.requests[1].options.signal.aborted).toBe(true);
        await complete(fixture.requests[1], [hit('stale on clear')]);
        await tick(250);
        expect(terms(fixture)).toEqual(['en', 'eng']);
        closed(fixture);
        expect(fixture.error).not.toHaveBeenCalled();
    });

    it('invalidates a search on question navigation, even when the previous index and query are reused', async () => {
        const fixture = await harness();
        fixture.type('english');
        fixture.press('Enter');
        await tick(0);
        fixture.get('next').click();
        expect(fixture.state().current).toBe(1);
        fixture.type('en');
        const original = fixture.input();
        await tick(250);
        fixture.get('prev').click();
        expect(fixture.state().current).toBe(0);
        expect(fixture.requests[0].options.signal.aborted).toBe(true);
        expect(fixture.item().wordSuggestionsBySlot[0]).toEqual([]);
        fixture.get('next').click();
        expect(fixture.state().current).toBe(1);
        fixture.input().focus();
        expect(fixture.input()).not.toBe(original);
        expect(fixture.input().value).toBe('en');
        await complete(fixture.requests[0], [hit('wrong visit')]);
        closed(fixture);
        expect(fixture.item().wordSuggestionsBySlot[0]).toEqual([]);
        fixture.type('en');
        await tick(250);
        await complete(fixture.requests[1], [hit('new visit')]);
        expect(fixture.suggestions()).toEqual(['new visit']);
    });

    it('does not apply a queued focus from an old render after returning to the same question', async () => {
        const fixture = await harness();
        fixture.type('english');
        fixture.press('Enter');
        await tick(0);
        fixture.get('next').click();
        fixture.run('focusManualAnswer(1, 0)');
        fixture.get('prev').click();
        fixture.get('next').click();
        fixture.get('outside').focus();
        await tick(1);
        expect(fixture.document.activeElement).toBe(fixture.get('outside'));
        fixture.run('focusManualAnswer(1, 0)');
        await tick(1);
        expect(fixture.document.activeElement).toBe(fixture.input());
    });

    it('Enter cancels obsolete work when the active word, gap, and completed question change', async () => {
        const fixture = await harness({ questions: [question(1, ['english class', 'school'])] });
        fixture.type('english');
        await tick(250);
        expect(fixture.press('Enter').defaultPrevented).toBe(true);
        await tick(1);
        expect(fixture.input().dataset.manualWord).toBe('1');
        expect(fixture.item().chosen[0]).toBeNull();
        expect(fixture.requests[0].options.signal.aborted).toBe(true);
        fixture.type('class');
        await tick(250);
        await complete(fixture.requests[0], [hit('late first word')]);
        closed(fixture);
        fixture.press('Enter');
        await tick(1);
        expect(fixture.input().dataset.manualGap).toBe('1');
        expect(fixture.item().chosen[0]).toBe('english class');
        expect(fixture.requests[1].options.signal.aborted).toBe(true);
        fixture.type('school');
        await tick(250);
        await complete(fixture.requests[1], [hit('late previous gap')]);
        closed(fixture);
        fixture.press('Enter');
        expect(fixture.requests[2].options.signal.aborted).toBe(true);
        await complete(fixture.requests[2], [hit('late completed question')]);
        expect(fixture.item().done).toBe(true);
        expect(fixture.item().chosen).toEqual(['english class', 'school']);
        expect(fixture.state().correct).toBe(1);
        expect(fixture.stored().items[0].chosen).toEqual(['english class', 'school']);
        closed(fixture);
    });

    it.each(['pointerdown', 'click', 'keyboard'])('%s still selects a suggestion and confirms it exactly once', async kind => {
        const fixture = await harness();
        fixture.type('en');
        await tick(250);
        await complete(fixture.requests[0], [hit('english')]);
        const button = fixture.list().querySelector('button[data-word-suggestion]');
        if (kind === 'keyboard') {
            expect(fixture.press('Tab').defaultPrevented).toBe(true);
            expect(button.dataset.testSuggestionActive).toBe('true');
            expect(fixture.press('Enter').defaultPrevented).toBe(true);
        } else {
            const event = new fixture.window.Event(kind, { bubbles: true, cancelable: true });
            button.dispatchEvent(event);
            expect(event.defaultPrevented).toBe(true);
        }
        expect(fixture.item().chosen).toEqual(['english']);
        expect(fixture.state().correct).toBe(1);
        expect(fixture.stored().items[0].done).toBe(true);
        closed(fixture);
    });

    it.each([80, 250])('cancels work at restart after %s ms before waiting for the reset response', async wait => {
        const fixture = await harness();
        fixture.type('en');
        await tick(wait);
        fixture.window.JS_TEST_PERSISTENCE.endpoint = '/fixture/state';
        fixture.get('restart-test').click();
        await tick(0);
        expect(fixture.resets).toHaveLength(1);
        expect(fixture.boundary).not.toHaveBeenCalled();
        if (wait === 250) {
            expect(fixture.requests[0].options.signal.aborted).toBe(true);
            await complete(fixture.requests[0], [hit('late during restart')]);
        } else {
            await tick(250);
            expect(terms(fixture)).toEqual([]);
        }
        closed(fixture);
        fixture.reset.resolve({ ok: true, redirected: false });
        await tick(0);
        expect(fixture.boundary).toHaveBeenCalledOnce();
        expect(fixture.storedRaw()).toBeNull();
        expect(fixture.error).not.toHaveBeenCalled();
    });

    it('round-trips only draft and ordinary progress through actual local persistence', async () => {
        const fixture = await harness();
        fixture.type('eng');
        await tick(80);
        fixture.window.dispatchEvent(new fixture.window.PageTransitionEvent('pagehide'));
        const saved = fixture.storedRaw();
        expect(terms(fixture)).toEqual([]);
        const reopened = await harness({ saved });
        expect(reopened.input().value).toBe('eng');
        expect(reopened.item().chosen).toEqual([null]);
        expect(reopened.item().manualWordIndexBySlot).toEqual([0]);
        expect(reopened.item().attemptsBySlot).toEqual([0]);
        expect(reopened.item().activeSlot).toBe(0);
        expect(reopened.item().feedback).toBe('');
        closed(reopened);
        await tick(250);
        expect(terms(fixture)).toEqual([]);
        expect(terms(reopened)).toEqual([]);
    });
});
