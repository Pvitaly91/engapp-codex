// @vitest-environment node
import fs from 'node:fs';
import vm from 'node:vm';
import { JSDOM } from 'jsdom';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

const layout = fs.readFileSync('resources/views/layouts/catalog-public.blade.php', 'utf8');
const component = layout.match(/^ {8}function searchBox\(\) \{[\s\S]*?^ {8}\}/m)?.[0];
const mark = '<mark class="rounded bg-amber-200/80 px-1 text-slate-900">';

function deferred() {
    let resolve;
    let reject;
    const promise = new Promise((yes, no) => { resolve = yes; reject = no; });
    return { promise, resolve, reject };
}

function harness(locale = 'uk') {
    const endpoint = locale === 'uk' ? '/search' : `/${locale}/search`;
    const requests = [];
    const location = { href: '' };
    const fetch = vi.fn((url, options) => {
        const pending = deferred();
        requests.push({ url, options, ...pending });

        // A test transport deliberately ignores abort(). The guard, not the
        // transport, must reject late successes, errors and JSON completion.
        return pending.promise;
    });
    // Use the production Blade factory, substituting only its server-rendered URL.
    const factory = vm.runInNewContext(component.replaceAll("{{ localized_route('site.search') }}", endpoint) + '\nsearchBox;', {
        window: { location },
        fetch,
        AbortController,
        setTimeout,
        clearTimeout,
    });

    return { box: factory(), factory, fetch, requests, location, endpoint };
}

function enter(box, value) {
    // Alpine's x-model updates query before the input handler is invoked.
    box.query = value;
    box.autocomplete();
}

const advance = ms => vi.advanceTimersByTimeAsync(ms);
const settled = () => advance(0);
const hit = (title, url = `/theory/${title.toLowerCase()}`, type = 'page') => ({ title, url, type });
const phrases = requests => requests.map(request => new URL(request.url, 'http://gramlyze.loc').searchParams.get('q'));

async function resolve(request, body) {
    request.resolve({ ok: true, status: 200, json: async () => body });
    await settled();
}

function closed(box) {
    expect(box.open).toBe(false);
    expect(box.results).toEqual([]);
}

beforeEach(() => vi.useFakeTimers({ toFake: ['setTimeout', 'clearTimeout'] }));
afterEach(() => {
    vi.clearAllTimers();
    vi.useRealTimers();
});

describe('shared site-search autocomplete', () => {
    it('sends only the final trimmed phrase after 250 ms of quiet', async () => {
        const { box, requests, fetch } = harness();

        for (const text of ['p', 'pr', 'pre', 'pres', 'prese', 'presen']) {
            enter(box, text);
            await advance(90);
        }

        enter(box, '  present  ');
        await advance(249);
        expect(fetch).not.toHaveBeenCalled();
        closed(box);
        await advance(1);
        expect(phrases(requests)).toEqual(['present']);
        expect(requests[0].options.headers).toMatchObject({ Accept: 'application/json' });

        const results = [hit('Present Perfect'), hit('Present Simple', '/test/present-simple', 'test')];
        await resolve(requests[0], results);
        expect(box.open).toBe(true);
        expect(box.results).toEqual(results);
    });

    it('sends separate requests when the user pauses longer than 250 ms', async () => {
        const { box, requests } = harness();

        for (const term of ['pr', 'pres', 'present']) {
            enter(box, term);
            closed(box);
            await advance(300);
            await resolve(requests.at(-1), [hit(term)]);
            expect(box.open).toBe(true);
            expect(box.results).toEqual([hit(term)]);
        }

        expect(phrases(requests)).toEqual(['pr', 'pres', 'present']);
        expect(requests.every(request => !request.options.signal.aborted)).toBe(true);
    });

    it('immediately invalidates the previous request during the next debounce', async () => {
        const { box, requests } = harness();
        enter(box, 'first');
        await advance(250);
        enter(box, 'second');

        expect(requests[0].options.signal.aborted).toBe(true);
        await resolve(requests[0], [hit('Stale')]);
        closed(box);
        expect(phrases(requests)).toEqual(['first']);
        await advance(250);
        await resolve(requests[1], [hit('Current')]);
        expect(box.results).toEqual([hit('Current')]);
    });

    it('does not overwrite results when responses arrive in reverse order', async () => {
        const { box, requests } = harness();
        enter(box, 'first');
        await advance(250);
        enter(box, 'second');
        await advance(250);

        await resolve(requests[1], [hit('Current')]);
        await resolve(requests[0], [hit('Stale')]);
        expect(box.open).toBe(true);
        expect(box.results).toEqual([hit('Current')]);
    });

    it('uses request identity for A → B → A, not just the current text', async () => {
        const { box, requests } = harness();
        for (const text of ['alpha', 'beta', 'alpha']) {
            enter(box, text);
            await advance(250);
        }

        expect(phrases(requests)).toEqual(['alpha', 'beta', 'alpha']);
        expect(requests.map(request => request.options.signal.aborted)).toEqual([true, true, false]);
        await resolve(requests[2], [hit('Current Alpha')]);
        await resolve(requests[1], [hit('Stale Beta')]);
        await resolve(requests[0], [hit('First Alpha')]);
        expect(box.results).toEqual([hit('Current Alpha')]);
    });

    it('checks request identity again after asynchronous response.json()', async () => {
        const { box, requests } = harness();
        enter(box, 'old phrase');
        await advance(250);
        const pendingJson = deferred();
        const json = vi.fn(() => pendingJson.promise);

        requests[0].resolve({ ok: true, status: 200, json });
        await settled();
        expect(json).toHaveBeenCalledOnce();
        enter(box, 'new phrase');
        await advance(250);
        await resolve(requests[1], [hit('New')]);
        pendingJson.resolve([hit('Old')]);
        await settled();

        expect(box.open).toBe(true);
        expect(box.results).toEqual([hit('New')]);
    });

    const closures = [
        ['clear', box => enter(box, '')],
        ['one trimmed character', box => enter(box, ' x ')],
        ['Escape', box => box.close()],
        ['submit', box => box.go()],
        ['destroy', box => box.destroy()],
    ];

    it.each(closures)('%s cancels pending work before a request is sent', async (name, close) => {
        const { box, fetch } = harness();
        enter(box, 'present');
        await advance(100);
        close(box);
        await advance(600);
        expect(fetch).not.toHaveBeenCalled();
        closed(box);
    });

    it.each(closures)('%s closes even if an in-flight request still responds', async (name, close) => {
        const { box, requests } = harness();
        enter(box, 'present');
        await advance(250);
        close(box);
        expect(requests[0].options.signal.aborted).toBe(true);
        await resolve(requests[0], [hit('Late')]);
        closed(box);
    });

    const failures = [
        ['HTTP error', request => {
            const json = vi.fn(async () => [hit('Should not apply')]);
            request.resolve({ ok: false, status: 503, json });
        }],
        ['network error', request => request.reject(new TypeError('Network error'))],
        ['invalid JSON', request => request.resolve({ ok: true, status: 200, json: async () => { throw new SyntaxError('Invalid JSON'); } })],
        ['non-array response', request => request.resolve({ ok: true, status: 200, json: async () => ({ items: [hit('Not a list')] }) })],
        ['malformed row', request => request.resolve({ ok: true, status: 200, json: async () => [hit('Valid row'), { title: 42, url: '/theory/invalid', type: 'page' }] })],
        ['abort', request => request.reject(Object.assign(new Error('Aborted'), { name: 'AbortError' }))],
    ];

    it.each(failures)('handles current %s without leaving old results', async (name, fail) => {
        const { box, requests } = harness();
        enter(box, 'first');
        await advance(250);
        await resolve(requests[0], [hit('Old result')]);
        enter(box, 'second');
        closed(box);
        await advance(250);
        fail(requests[1]);
        await settled();
        closed(box);
    });

    it.each(failures)('ignores late %s once fresh results are shown', async (name, fail) => {
        const { box, requests } = harness();
        enter(box, 'first');
        await advance(250);
        enter(box, 'second');
        await advance(250);
        await resolve(requests[1], [hit('Current')]);
        fail(requests[0]);
        await settled();
        expect(box.open).toBe(true);
        expect(box.results).toEqual([hit('Current')]);
    });

    it('does not let stale JSON rejection and finally discard the active request controller', async () => {
        const { box, requests } = harness();
        enter(box, 'first');
        await advance(250);
        const pendingJson = deferred();
        const json = vi.fn(() => pendingJson.promise);
        requests[0].resolve({ ok: true, status: 200, json });
        await settled();
        expect(json).toHaveBeenCalledOnce();

        enter(box, 'second');
        await advance(250);
        pendingJson.reject(new SyntaxError('Late JSON error'));
        await settled();

        box.close();
        expect(requests[1].options.signal.aborted).toBe(true);
        await resolve(requests[1], [hit('Closed')]);
        closed(box);
    });

    it('retains the existing successful empty-result state', async () => {
        const { box, requests } = harness();
        enter(box, 'no matches');
        await advance(250);
        await resolve(requests[0], []);
        expect(box.results).toEqual([]);
        expect(box.open).toBe(true);
    });

    it.each(['uk', 'en', 'pl'])('keeps %s navigation, query encoding and the received links', async locale => {
        const { box, requests, location, endpoint } = harness(locale);
        const query = 'підмет + [A&B] #?';
        enter(box, `  ${query}  `);
        await advance(250);
        const url = `${endpoint}?q=${encodeURIComponent(query)}`;
        expect(requests[0].url).toBe(url);
        expect(phrases(requests)).toEqual([query]);
        const prefix = locale === 'uk' ? '' : `/${locale}`;
        const results = [hit('Підмет', `${prefix}/theory/basic-grammar/subject`), hit('A & B', `${prefix}/test/ab?source=search#start`, 'test')];
        await resolve(requests[0], results);
        expect(box.results).toEqual(results);
        box.go();
        expect(location.href).toBe(url);
        closed(box);
    });

    it('submits immediately without waiting for a timer or response', async () => {
        const { box, requests, location } = harness('pl');
        enter(box, 'older');
        await advance(250);
        enter(box, '  czy + jak  ');
        box.go();
        expect(location.href).toBe('/pl/search?q=czy%20%2B%20jak');
        expect(requests[0].options.signal.aborted).toBe(true);
        await resolve(requests[0], [hit('Late')]);
        await advance(250);
        expect(phrases(requests)).toEqual(['older']);
        closed(box);
    });

    it('highlights Ukrainian text and literal regex characters without exposing HTML', () => {
        const { box } = harness();
        box.query = 'підмет + [a] (b)?';
        expect(box.highlight('Підмет + [A] (B)? & <img src="x">')).toBe(
            `${mark}Підмет + [A] (B)?</mark> &amp; &lt;img src=&quot;x&quot;&gt;`,
        );
    });

    it('binds desktop and mobile to the actual shared component and closes only the hidden mobile one', async () => {
        const dom = new JSDOM(layout);
        try {
            const { document, CustomEvent } = dom.window;
            const fields = Array.from(document.querySelectorAll('[x-data="searchBox()"]'));
            expect(fields).toHaveLength(2);
            for (const field of fields) {
                const input = field.querySelector('input[type="search"]');
                expect(input.getAttribute('x-model')).toBe('query');
                expect(input.getAttribute('@input')).toBe('autocomplete');
                expect(input.getAttribute('@keydown.escape')).toBe('close()');
                expect(field.querySelector('form').getAttribute('@submit.prevent')).toBe('go');
                expect(field.querySelector('template[x-for="item in results"]').content.querySelector('a').getAttribute(':href')).toBe('item.url');
            }

            const event = 'site-search-mobile-closed';
            const eventAttribute = `@${event}.window`;
            expect(fields[0].getAttribute(eventAttribute)).toBeNull();
            expect(fields[1].closest('[x-show="mobile"]')).not.toBeNull();
            const closeExpression = fields[1].getAttribute(eventAttribute);
            expect(closeExpression).toBe('close()');
            const action = Array.from(document.querySelectorAll('button'), button => button.getAttribute('@click'))
                .find(expression => expression?.includes('mobile = !mobile'));
            const toggle = scope => new Function('$dispatch', `with (this) { ${action} }`).call(scope, name => dom.window.dispatchEvent(new CustomEvent(name)));
            const { box: desktop, factory, requests } = harness();
            const mobile = factory();
            dom.window.addEventListener(event, () => new Function(`with (this) { ${closeExpression} }`).call(mobile));

            const menu = { mobile: true };
            enter(desktop, 'desktop');
            await advance(100);
            enter(mobile, 'mobile');
            await advance(150);
            expect(phrases(requests)).toEqual(['desktop']);
            await advance(100);
            expect(phrases(requests)).toEqual(['desktop', 'mobile']);
            toggle(menu);
            expect(menu.mobile).toBe(false);
            expect(requests.map(request => request.options.signal.aborted)).toEqual([false, true]);

            await resolve(requests[0], [hit('From desktop')]);
            toggle(menu);
            expect(menu.mobile).toBe(true);
            await resolve(requests[1], [hit('From hidden mobile')]);
            expect(desktop.open).toBe(true);
            expect(desktop.results).toEqual([hit('From desktop')]);
            closed(mobile);

            enter(mobile, 'second mobile');
            await advance(100);
            toggle(menu);
            toggle(menu);
            await advance(250);
            expect(phrases(requests)).toEqual(['desktop', 'mobile']);
            closed(mobile);
        } finally {
            dom.window.close();
        }
    });
});
