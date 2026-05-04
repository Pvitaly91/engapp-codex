<script>
window.FRONTEND_TESTS_I18N = @json(__('frontend.tests'));
window.__IS_ADMIN__ = Boolean(@json($isAdmin ?? false));
window.__SHOW_TEST_TECH_INFO__ = Boolean(@json($showTechnicalInfo ?? false));
window.__QUESTION_REPORT_ENDPOINT__ = @json(route('question-reports.store'));
window.__QUESTION_REPORT_TEST_SLUG__ = @json((string) data_get($test ?? null, 'slug', ''));
window.__QUESTION_REPORT_TEST_NAME__ = @json((string) data_get($test ?? null, 'name', ''));
window.__QUESTION_REPORT_MODE__ = @json((string) ($jsStateMode ?? ''));

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

function techInfoUi(key, fallback = '') {
    return testUi(`tech_info.${key}`, {}, fallback);
}

function hasTechnicalValue(value) {
    if (Array.isArray(value)) {
        return value.length > 0;
    }

    return value !== null && value !== undefined && String(value).trim() !== '';
}

function technicalValue(value) {
    return hasTechnicalValue(value) ? String(value) : techInfoUi('not_available', 'N/A');
}

function renderTechnicalInfoField(label, value, options = {}) {
    const classes = ['rounded-xl border px-3 py-2.5'];
    if (options.colSpan) {
        classes.push('sm:col-span-2');
    }

    const primaryTag = options.code ? 'code' : 'div';
    const primaryClass = options.code
        ? 'mt-1 block break-all font-mono text-[12px] leading-5'
        : 'mt-1 text-[13px] leading-5';
    const secondary = hasTechnicalValue(options.secondary)
        ? `<div class="mt-1 break-all font-mono text-[11px] leading-5" style="color:#64748b;">${html(options.secondary)}</div>`
        : '';

    return `
        <div class="${classes.join(' ')}" style="border-color:#dbe4f0;background:#f8fbff;">
            <div class="text-[10px] font-extrabold uppercase tracking-[0.14em]" style="color:#64748b;">${html(label)}</div>
            <${primaryTag} class="${primaryClass}" style="color:#0f172a;">${html(technicalValue(value))}</${primaryTag}>
            ${secondary}
        </div>
    `;
}

function getTechnicalQuestions() {
    return Array.isArray(window.__INITIAL_JS_TEST_QUESTIONS__)
        ? window.__INITIAL_JS_TEST_QUESTIONS__
        : [];
}

function getMatchQuestionByKey(key) {
    if (!key) {
        return null;
    }

    const questions = getTechnicalQuestions();
    if (key.startsWith('q-')) {
        const id = key.slice(2);
        return questions.find((question) => String(question?.id ?? '') === id) || null;
    }

    if (key.startsWith('idx-')) {
        const index = Number.parseInt(key.slice(4), 10);
        return Number.isInteger(index) ? (questions[index] || null) : null;
    }

    return null;
}

function resolveTechnicalQuestion(container) {
    const questions = getTechnicalQuestions();

    if (container.matches('article[data-idx]')) {
        const index = Number.parseInt(container.dataset.idx || '', 10);
        return Number.isInteger(index) ? (questions[index] || null) : null;
    }

    if (container.classList.contains('drag-quiz__row')) {
        const rows = Array.from(container.parentElement?.querySelectorAll('.drag-quiz__row') || []);
        const index = rows.indexOf(container);
        return index >= 0 ? (questions[index] || null) : null;
    }

    if (container.classList.contains('match-card') && container.classList.contains('match-sentence')) {
        return getMatchQuestionByKey(container.dataset.key || '');
    }

    return null;
}

function renderTechnicalInfoBlock(question, context = 'default') {
    if (!window.__SHOW_TEST_TECH_INFO__ || !question || !question.tech_info) {
        return '';
    }

    const info = question.tech_info || {};
    const source = info.source || {};
    const seeder = info.seeder || {};
    const type = info.type || {};
    const markers = Array.isArray(info.markers) ? info.markers.join(', ') : '';
    const tags = Array.isArray(info.tags) ? info.tags.join(', ') : '';
    const typeValue = hasTechnicalValue(type.label)
        ? `${type.label}${hasTechnicalValue(type.value) ? ` (${type.value})` : ''}`
        : type.value;
    const wrapperStyles = context === 'drag'
        ? 'grid-column: 2 / -1; margin-top: 10px;'
        : 'margin-top: 14px;';

    const fields = [
        renderTechnicalInfoField(techInfoUi('question_id', 'Question ID'), info.question_id),
        renderTechnicalInfoField(techInfoUi('question_uuid', 'Question UUID'), info.question_uuid, { code: true }),
        renderTechnicalInfoField(techInfoUi('difficulty', 'Difficulty'), info.difficulty),
        renderTechnicalInfoField(techInfoUi('level', 'Level'), info.level),
        renderTechnicalInfoField(techInfoUi('category', 'Category'), info.category),
        renderTechnicalInfoField(techInfoUi('source', 'Source'), source.name, {
            secondary: hasTechnicalValue(source.id)
                ? `${techInfoUi('source_id', 'Source ID')}: ${source.id}`
                : '',
            colSpan: true,
        }),
        renderTechnicalInfoField(techInfoUi('seeder', 'Seeder'), seeder.name || seeder.class, {
            secondary: seeder.class && seeder.class !== seeder.name ? seeder.class : '',
            code: true,
            colSpan: true,
        }),
        renderTechnicalInfoField(techInfoUi('seeder_run', 'Latest seeder run'), seeder.last_ran_at),
        renderTechnicalInfoField(techInfoUi('type', 'Type'), typeValue),
        renderTechnicalInfoField(techInfoUi('markers', 'Markers'), markers || null),
        renderTechnicalInfoField(techInfoUi('markers_count', 'Markers count'), info.markers_count),
        renderTechnicalInfoField(techInfoUi('answers_count', 'Answers count'), info.answers_count),
        renderTechnicalInfoField(techInfoUi('options_count', 'Options count'), info.options_count),
        renderTechnicalInfoField(techInfoUi('variants_count', 'Variants count'), info.variants_count),
        renderTechnicalInfoField(techInfoUi('theory_block', 'Theory block UUID'), info.theory_text_block_uuid, {
            code: true,
        }),
        renderTechnicalInfoField(techInfoUi('tags', 'Tags'), tags || null, { colSpan: true }),
        renderTechnicalInfoField(techInfoUi('created_at', 'Created at'), info.created_at),
        renderTechnicalInfoField(techInfoUi('updated_at', 'Updated at'), info.updated_at),
    ].join('');

    const v3NamespaceBlock = seeder.is_v3 && hasTechnicalValue(seeder.v3_path_after_ai)
        ? `
            <div class="rounded-2xl border px-3.5 py-3" style="border-color:#bfdbfe;background:linear-gradient(180deg,#eff6ff 0%,#f8fbff 100%);">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div class="text-[10px] font-extrabold uppercase tracking-[0.16em]" style="color:#1d4ed8;">${html(techInfoUi('v3_namespace', 'V3 namespace'))}</div>
                    <div class="rounded-full px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-[0.14em]" style="background:#dbeafe;color:#1e40af;">
                        ${html(techInfoUi('llm', 'LLM'))}: ${html(technicalValue(seeder.llm_display_name || seeder.llm))}
                    </div>
                </div>
                <code class="mt-2 block break-all font-mono text-[12px] leading-5" style="color:#0f172a;">${html(seeder.v3_path_after_ai)}</code>
            </div>
        `
        : '';

    return `
        <details class="js-tech-info-block rounded-2xl border" style="${wrapperStyles} border-color:#cbd5e1;background:#ffffff;">
            <summary class="cursor-pointer list-none px-4 py-3 text-[11px] font-extrabold uppercase tracking-[0.18em]" style="color:#334155;">
                ${html(techInfoUi('summary', 'Technical info'))}
            </summary>
            <div class="border-t px-4 pb-4 pt-3" style="border-color:#e2e8f0;">
                <div class="space-y-3">
                    ${v3NamespaceBlock}
                    <div class="grid gap-2 sm:grid-cols-2">
                        ${fields}
                    </div>
                </div>
            </div>
        </details>
    `;
}

function questionReportUi(key, fallback = '') {
    return testUi(`question_report.${key}`, {}, fallback);
}

function reportQuestionId(question) {
    const id = question?.id ?? question?.question_id ?? question?.tech_info?.question_id ?? '';

    return String(id ?? '').trim();
}

function reportQuestionUuid(question) {
    const uuid = question?.uuid ?? question?.question_uuid ?? question?.tech_info?.question_uuid ?? '';

    return String(uuid ?? '').trim();
}

function reportQuestionText(question) {
    return String(question?.question ?? question?.sourceTextUk ?? question?.text ?? '').trim();
}

function renderQuestionReportBlock(question, context = 'default') {
    if (!window.__IS_ADMIN__ || !question) {
        return '';
    }

    const questionId = reportQuestionId(question);
    const questionUuid = reportQuestionUuid(question);

    if (!questionId && !questionUuid) {
        return '';
    }

    const questionText = reportQuestionText(question);
    const wrapperStyles = context === 'drag'
        ? 'grid-column: 2 / -1; margin-top: 10px;'
        : 'margin-top: 14px;';

    return `
        <div class="js-question-report-block rounded-2xl border" style="${wrapperStyles} border-color:#fed7aa;background:#fff7ed;"
             data-question-id="${html(questionId)}"
             data-question-uuid="${html(questionUuid)}"
             data-question-text="${html(questionText)}">
            <div class="flex flex-wrap items-center justify-between gap-2 px-4 py-3">
                <div>
                    <div class="text-[10px] font-extrabold uppercase tracking-[0.16em]" style="color:#c2410c;">${html(questionReportUi('admin_only', 'Admin only'))}</div>
                    <div class="mt-1 text-sm font-semibold" style="color:#7c2d12;">${html(questionReportUi('title', 'Report question issue'))}</div>
                </div>
                <button type="button"
                    class="rounded-xl border px-3 py-2 text-xs font-extrabold uppercase tracking-[0.12em] transition hover:bg-orange-100"
                    style="border-color:#fdba74;color:#9a3412;"
                    data-question-report-toggle>
                    ${html(questionReportUi('toggle', 'Report'))}
                </button>
            </div>
            <form class="hidden border-t px-4 pb-4 pt-3" style="border-color:#fed7aa;" data-question-report-form>
                <label class="block text-xs font-bold uppercase tracking-[0.14em]" style="color:#9a3412;">
                    ${html(questionReportUi('comment_label', 'Comment'))}
                </label>
                <textarea
                    class="mt-2 min-h-[84px] w-full rounded-xl border px-3 py-2 text-sm leading-6 focus:outline-none focus:ring-2"
                    style="border-color:#fdba74;--tw-ring-color:#fb923c;"
                    name="comment"
                    maxlength="4000"
                    required
                    placeholder="${html(questionReportUi('comment_placeholder', 'Describe what is wrong in the question or answer.'))}"></textarea>
                <div class="mt-3 flex flex-wrap items-center gap-3">
                    <button type="submit"
                        class="rounded-xl bg-orange-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-orange-700"
                        data-question-report-submit>
                        ${html(questionReportUi('submit', 'Send report'))}
                    </button>
                    <span class="text-sm" style="color:#9a3412;" data-question-report-status></span>
                </div>
            </form>
        </div>
    `;
}

function resolveReportQuestion(container) {
    return resolveTechnicalQuestion(container);
}

function injectQuestionReportBlock(container) {
    if (!window.__IS_ADMIN__ || !container) {
        return;
    }

    const hasExisting = Array.from(container.children || []).some((child) => child.classList?.contains('js-question-report-block'));
    if (hasExisting) {
        return;
    }

    const question = resolveReportQuestion(container);
    const context = container.classList.contains('drag-quiz__row')
        ? 'drag'
        : (container.classList.contains('match-card') ? 'match' : 'default');
    const blockHtml = renderQuestionReportBlock(question, context);

    if (!blockHtml) {
        return;
    }

    const wrapper = document.createElement('div');
    wrapper.innerHTML = blockHtml.trim();
    const block = wrapper.firstElementChild;

    if (block) {
        container.appendChild(block);
    }
}

function enhanceQuestionReportBlocks() {
    if (!window.__IS_ADMIN__) {
        return;
    }

    document.querySelectorAll('article[data-idx]').forEach(injectQuestionReportBlock);
    document.querySelectorAll('.drag-quiz__row').forEach(injectQuestionReportBlock);
    document.querySelectorAll('.match-card.match-sentence').forEach(injectQuestionReportBlock);
}

let questionReportFrameId = null;

function scheduleQuestionReportEnhancement() {
    if (!window.__IS_ADMIN__) {
        return;
    }

    if (questionReportFrameId !== null) {
        return;
    }

    questionReportFrameId = window.requestAnimationFrame(() => {
        questionReportFrameId = null;
        enhanceQuestionReportBlocks();
    });
}

function questionReportPayload(block, form) {
    const formData = new FormData(form);

    return {
        question_id: block.dataset.questionId || null,
        question_uuid: block.dataset.questionUuid || null,
        comment: String(formData.get('comment') || '').trim(),
        test_slug: window.__QUESTION_REPORT_TEST_SLUG__ || null,
        test_name: window.__QUESTION_REPORT_TEST_NAME__ || null,
        mode: window.__QUESTION_REPORT_MODE__ || null,
        url: window.location.href,
    };
}

async function submitQuestionReport(form) {
    const block = form.closest('.js-question-report-block');
    const status = block?.querySelector('[data-question-report-status]');
    const submitButton = form.querySelector('[data-question-report-submit]');
    const payload = questionReportPayload(block, form);

    if (!payload.comment) {
        if (status) {
            status.textContent = questionReportUi('comment_required', 'Enter a comment.');
        }
        return;
    }

    if (!window.__QUESTION_REPORT_ENDPOINT__) {
        if (status) {
            status.textContent = questionReportUi('error', 'Unable to save report.');
        }
        return;
    }

    if (submitButton) {
        submitButton.disabled = true;
        submitButton.classList.add('opacity-60');
    }
    if (status) {
        status.textContent = questionReportUi('saving', 'Saving...');
    }

    try {
        const response = await fetch(window.__QUESTION_REPORT_ENDPOINT__, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
            },
            credentials: 'same-origin',
            body: JSON.stringify(payload),
        });

        if (!response.ok) {
            throw new Error('Failed to save question report');
        }

        const data = await response.json();
        form.reset();
        if (status) {
            status.textContent = `${questionReportUi('saved', 'Saved')}: ${data?.report?.file || ''}`;
        }
        if (block) {
            block.dataset.reported = '1';
        }
    } catch (error) {
        console.error(error);
        if (status) {
            status.textContent = questionReportUi('error', 'Unable to save report.');
        }
    } finally {
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.classList.remove('opacity-60');
        }
    }
}

function setupQuestionReportControls() {
    if (!window.__IS_ADMIN__) {
        return;
    }

    scheduleQuestionReportEnhancement();

    document.addEventListener('click', (event) => {
        const toggle = event.target.closest('[data-question-report-toggle]');
        if (!toggle) {
            return;
        }

        event.preventDefault();
        const block = toggle.closest('.js-question-report-block');
        const form = block?.querySelector('[data-question-report-form]');
        if (!form) {
            return;
        }

        form.classList.toggle('hidden');
        if (!form.classList.contains('hidden')) {
            form.querySelector('textarea')?.focus();
        }
    });

    document.addEventListener('submit', (event) => {
        const form = event.target.closest('[data-question-report-form]');
        if (!form) {
            return;
        }

        event.preventDefault();
        submitQuestionReport(form);
    });

    if (typeof MutationObserver === 'undefined' || !document.body) {
        return;
    }

    const observer = new MutationObserver(() => {
        scheduleQuestionReportEnhancement();
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true,
    });
}

function injectTechnicalInfoBlock(container) {
    if (!window.__SHOW_TEST_TECH_INFO__ || !container) {
        return;
    }

    const question = resolveTechnicalQuestion(container);
    if (!question || !question.tech_info) {
        return;
    }

    const hasExisting = Array.from(container.children || []).some((child) => child.classList?.contains('js-tech-info-block'));
    if (hasExisting) {
        return;
    }

    const context = container.classList.contains('drag-quiz__row')
        ? 'drag'
        : (container.classList.contains('match-card') ? 'match' : 'default');
    const blockHtml = renderTechnicalInfoBlock(question, context);

    if (!blockHtml) {
        return;
    }

    const wrapper = document.createElement('div');
    wrapper.innerHTML = blockHtml.trim();
    const block = wrapper.firstElementChild;

    if (block) {
        container.appendChild(block);
    }
}

let technicalInfoFrameId = null;

function enhanceTechnicalInfoBlocks() {
    if (!window.__SHOW_TEST_TECH_INFO__) {
        return;
    }

    document.querySelectorAll('article[data-idx]').forEach(injectTechnicalInfoBlock);
    document.querySelectorAll('.drag-quiz__row').forEach(injectTechnicalInfoBlock);
    document.querySelectorAll('.match-card.match-sentence').forEach(injectTechnicalInfoBlock);
}

function scheduleTechnicalInfoEnhancement() {
    if (!window.__SHOW_TEST_TECH_INFO__) {
        return;
    }

    if (technicalInfoFrameId !== null) {
        return;
    }

    technicalInfoFrameId = window.requestAnimationFrame(() => {
        technicalInfoFrameId = null;
        enhanceTechnicalInfoBlocks();
    });
}

function setupTechnicalInfoObserver() {
    if (!window.__SHOW_TEST_TECH_INFO__) {
        return;
    }

    scheduleTechnicalInfoEnhancement();

    if (typeof MutationObserver === 'undefined' || !document.body) {
        return;
    }

    const observer = new MutationObserver(() => {
        scheduleTechnicalInfoEnhancement();
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true,
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupTechnicalInfoObserver, { once: true });
    document.addEventListener('DOMContentLoaded', setupQuestionReportControls, { once: true });
} else {
    setupTechnicalInfoObserver();
    setupQuestionReportControls();
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
