<script>
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

    const snapshot = cloneState(state);
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

    if (immediate) {
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
</script>
