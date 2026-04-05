<script>
window.FRONTEND_TESTS_I18N = @json(__('frontend.tests'));

function getTestUiValue(path, fallback = '') {
    const source = window.FRONTEND_TESTS_I18N || {};
    const value = String(path || '')
        .split('.')
        .reduce((acc, segment) => {
            if (acc && typeof acc === 'object' && Object.prototype.hasOwnProperty.call(acc, segment)) {
                return acc[segment];
            }

            return undefined;
        }, source);

    return value === undefined ? fallback : value;
}

function interpolateTestUi(template, replacements = {}) {
    return String(template ?? '').replace(/:([a-zA-Z0-9_]+)/g, (match, key) => {
        if (Object.prototype.hasOwnProperty.call(replacements, key)) {
            return replacements[key];
        }

        return match;
    });
}

function testUi(path, replacements = {}, fallback = '') {
    return interpolateTestUi(getTestUiValue(path, fallback), replacements);
}

function shuffle(arr) {
    for (let i = arr.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [arr[i], arr[j]] = [arr[j], arr[i]];
    }
}
function pct(a, b) {
    return Math.round((a / (b || 1)) * 100);
}
function html(str) {
    return String(str)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function resizeSelect(el) {
    const span = document.createElement('span');
    span.style.visibility = 'hidden';
    span.style.position = 'absolute';
    span.style.whiteSpace = 'pre';
    span.style.font = getComputedStyle(el).font;
    span.textContent = el.options[el.selectedIndex]?.text || '';
    document.body.appendChild(span);
    const width = span.offsetWidth + 32;
    document.body.removeChild(span);
    el.style.width = width + 'px';
}

function cloneState(data) {
    if (data === null || data === undefined) {
        return null;
    }

    return JSON.parse(JSON.stringify(data));
}

function isFilledAnswerScalar(value) {
    if (Array.isArray(value)) {
        return value.some(isFilledAnswerScalar);
    }

    if (typeof value === 'string') {
        return value.trim() !== '';
    }

    if (typeof value === 'number') {
        return Number.isFinite(value);
    }

    return value !== null && value !== undefined && value !== false;
}

function isStartedState(state) {
    if (!state || typeof state !== 'object') {
        return false;
    }

    if (Array.isArray(state.connections) && state.connections.length > 0) {
        return true;
    }

    if (Array.isArray(state.placements) && state.placements.length > 0) {
        return true;
    }

    if (!Array.isArray(state.items)) {
        return false;
    }

    return state.items.some((item) => {
        if (!item || typeof item !== 'object') {
            return false;
        }

        return isFilledAnswerScalar(item.chosen) || isFilledAnswerScalar(item.inputs);
    });
}

function prepareStateForPersistence(state) {
    const snapshot = cloneState(state);

    if (!snapshot || typeof snapshot !== 'object') {
        return snapshot;
    }

    const meta = snapshot.__meta && typeof snapshot.__meta === 'object'
        ? snapshot.__meta
        : {};
    const started = isStartedState(snapshot);

    meta.started = started;

    if (started) {
        const questionData = cloneState(window.__INITIAL_JS_TEST_QUESTIONS__);
        meta.question_data = Array.isArray(questionData) ? questionData : [];
    } else {
        delete meta.question_data;
    }

    snapshot.__meta = meta;

    return snapshot;
}

const JS_TEST_PERSISTENCE = window.JS_TEST_PERSISTENCE || null;
let JS_TEST_SAVE_TIMER = null;

if (JS_TEST_PERSISTENCE && JS_TEST_PERSISTENCE.saved) {
    JS_TEST_PERSISTENCE.saved = cloneState(JS_TEST_PERSISTENCE.saved);
}

function getSavedState() {
    if (!JS_TEST_PERSISTENCE || !JS_TEST_PERSISTENCE.saved) {
        return null;
    }

    return cloneState(JS_TEST_PERSISTENCE.saved);
}

function persistState(state, immediate = false) {
    if (!JS_TEST_PERSISTENCE || !JS_TEST_PERSISTENCE.endpoint) {
        return;
    }

    const previousStarted = Boolean(JS_TEST_PERSISTENCE?.saved?.__meta?.started);
    const snapshot = prepareStateForPersistence(state);
    const started = Boolean(snapshot?.__meta?.started);
    JS_TEST_PERSISTENCE.saved = snapshot;

    const payload = {
        mode: JS_TEST_PERSISTENCE.mode,
        state: snapshot,
    };

    const send = () => {
        fetch(JS_TEST_PERSISTENCE.endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': JS_TEST_PERSISTENCE.token,
            },
            credentials: 'same-origin',
            body: JSON.stringify(payload),
        }).catch(() => {});
    };

    if (immediate || (started && !previousStarted)) {
        if (JS_TEST_SAVE_TIMER) {
            clearTimeout(JS_TEST_SAVE_TIMER);
        }
        send();
        return;
    }

    if (JS_TEST_SAVE_TIMER) {
        clearTimeout(JS_TEST_SAVE_TIMER);
    }
    JS_TEST_SAVE_TIMER = setTimeout(send, 250);
}

async function loadQuestions(forceFresh = false) {
    const current = Array.isArray(window.__INITIAL_JS_TEST_QUESTIONS__)
        ? window.__INITIAL_JS_TEST_QUESTIONS__
        : [];

    if (!forceFresh) {
        return current;
    }

    if (!JS_TEST_PERSISTENCE || !JS_TEST_PERSISTENCE.questionsEndpoint) {
        if (JS_TEST_PERSISTENCE) {
            JS_TEST_PERSISTENCE.saved = null;
        }

        window.__INITIAL_JS_TEST_QUESTIONS__ = current;

        return current;
    }

    try {
        const url = new URL(JS_TEST_PERSISTENCE.questionsEndpoint, window.location.origin);
        if (JS_TEST_PERSISTENCE.mode) {
            url.searchParams.set('mode', JS_TEST_PERSISTENCE.mode);
        }

        const response = await fetch(url.toString(), {
            headers: {
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error('Failed to load fresh questions');
        }

        const payload = await response.json();
        if (payload && Array.isArray(payload.questions)) {
            JS_TEST_PERSISTENCE.saved = null;
            window.__INITIAL_JS_TEST_QUESTIONS__ = payload.questions;

            return payload.questions;
        }
    } catch (error) {
        console.error(error);
    }

    if (JS_TEST_PERSISTENCE) {
        JS_TEST_PERSISTENCE.saved = null;
    }

    return current;
}

async function resetJsTestState() {
    if (!JS_TEST_PERSISTENCE) {
        return;
    }

    JS_TEST_PERSISTENCE.saved = null;

    if (!JS_TEST_PERSISTENCE.endpoint) {
        return;
    }

    try {
        await fetch(JS_TEST_PERSISTENCE.endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': JS_TEST_PERSISTENCE.token,
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                mode: JS_TEST_PERSISTENCE.mode,
                state: null,
            }),
        });
    } catch (error) {
        console.error(error);
    }
}

async function restartJsTest(initFn, options = {}) {
    if (typeof initFn !== 'function') {
        return;
    }

    const { showLoaderFn, button } = options;
    const toggleLoader = (value) => {
        if (typeof showLoaderFn === 'function') {
            try {
                showLoaderFn(Boolean(value));
            } catch (error) {
                console.error(error);
            }
        }
    };

    if (button) {
        button.disabled = true;
        button.classList.add('opacity-50');
    }

    try {
        toggleLoader(true);
        await resetJsTestState();
        await initFn(true);
    } catch (error) {
        console.error(error);
    } finally {
        toggleLoader(false);
        if (button) {
            button.disabled = false;
            button.classList.remove('opacity-50');
        }
    }
}
</script>
