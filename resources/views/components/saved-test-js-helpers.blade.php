@php
    $__questionReportAdmin = (bool) ($isAdmin ?? false);
    $__questionReportIssueCatalog = $__questionReportAdmin ? ($questionReportIssueCatalog ?? []) : [];
    $__questionReportsByQuestion = $__questionReportAdmin ? ($questionReportsByQuestion ?? []) : [];
    $__questionReportUi = $__questionReportAdmin ? ($questionReportUi ?? []) : [];
@endphp

<style>
    [data-question-reported="1"] {
        border-color: #f59e0b !important;
        background: #fffbeb !important;
        box-shadow: 0 0 0 1px rgba(245, 158, 11, 0.24);
    }

    @media (prefers-color-scheme: dark) {
        [data-question-reported="1"] {
            background: rgba(69, 26, 3, 0.3) !important;
        }
    }
</style>

<script>
window.FRONTEND_TESTS_I18N = @json(__('frontend.tests'));
window.__IS_ADMIN__ = Boolean(@json($isAdmin ?? false));
window.__SHOW_TEST_TECH_INFO__ = Boolean(@json($showTechnicalInfo ?? false));
window.__QUESTION_REPORT_ENDPOINT__ = @json($__questionReportAdmin ? route('question-reports.store') : null);
window.__QUESTION_REPORT_TEST_SLUG__ = @json((string) data_get($test ?? null, 'slug', ''));
window.__QUESTION_REPORT_TEST_NAME__ = @json((string) data_get($test ?? null, 'name', ''));
window.__QUESTION_REPORT_MODE__ = @json((string) ($jsStateMode ?? ''));
window.__QUESTION_REPORT_ISSUE_CATALOG__ = @json($__questionReportIssueCatalog);
window.__QUESTION_REPORT_BY_QUESTION__ = @json($__questionReportsByQuestion);
window.__QUESTION_REPORT_UI__ = @json($__questionReportUi);

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
    const theoryPage = info.theory_page || {};
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
        (hasTechnicalValue(theoryPage.title) || hasTechnicalValue(theoryPage.id) || hasTechnicalValue(theoryPage.slug))
            ? renderTechnicalInfoField(techInfoUi('theory_page', 'Theory page'), theoryPage.title || theoryPage.slug || theoryPage.id, {
                secondary: [
                    hasTechnicalValue(theoryPage.id) ? `${techInfoUi('theory_page_id', 'Theory page ID')}: ${theoryPage.id}` : '',
                    hasTechnicalValue(theoryPage.slug) ? `${techInfoUi('theory_page_slug', 'Theory page slug')}: ${theoryPage.slug}` : '',
                ].filter(Boolean).join(' · '),
                colSpan: true,
            })
            : '',
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

function getQuestionReportObjectValue(path) {
    const source = window.__QUESTION_REPORT_UI__ || {};
    return String(path || '')
        .split('.')
        .reduce((acc, segment) => {
            if (acc && typeof acc === 'object' && Object.prototype.hasOwnProperty.call(acc, segment)) {
                return acc[segment];
            }

            return undefined;
        }, source);
}

function questionReportUi(key, fallback = '') {
    const adminValue = getQuestionReportObjectValue(key);

    if (adminValue !== undefined) {
        return adminValue;
    }

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

function questionReportIssueCatalog() {
    const catalog = window.__QUESTION_REPORT_ISSUE_CATALOG__;

    return Array.isArray(catalog) ? catalog : [];
}

function questionReportIssueCheckboxes() {
    const items = questionReportIssueCatalog();
    if (items.length === 0) {
        return '';
    }

    const title = html(questionReportUi('title', 'Що не так із питанням?'));
    const note = html(questionReportUi('comment_optional', 'Коментар необов’язковий, якщо вибрано тип помилки'));
    const otherHint = html(questionReportUi('other_requires_comment_hint', 'Опишіть проблему в коментарі'));

    const rows = items.map((issue) => {
        const key = String(issue?.key ?? issue?.slug ?? '');
        const label = String(issue?.label ?? key);
        const description = String(issue?.description ?? '');
        if (key === '') {
            return '';
        }

        const labelText = html(label);
        const descriptionMarkup = description
            ? `<span class="mt-0.5 block text-xs leading-5" style="color:#9a3412;">${html(description)}</span>`
            : '';

        return `
            <label class="flex items-start gap-2 rounded-xl border px-3 py-2 transition hover:bg-orange-50"
                   style="border-color:#fed7aa;background:#fffaf2;">
                <input type="checkbox"
                       name="issue_types[]"
                       value="${html(key)}"
                       class="mt-1 h-4 w-4 rounded border-orange-300 text-orange-600 focus:ring-orange-400"
                       data-question-report-issue>
                <span class="block">
                    <span class="block text-sm font-semibold" style="color:#7c2d12;">${labelText}</span>
                    ${descriptionMarkup}
                </span>
            </label>
        `;
    }).filter(Boolean).join('');

    return `
        <div class="mb-3">
            <div class="text-sm font-bold" style="color:#7c2d12;">${title}</div>
            <p class="mt-1 text-xs" style="color:#b45309;">${note}</p>
            <div class="mt-2 grid gap-2 sm:grid-cols-2">${rows}</div>
            <p class="mt-2 hidden text-xs font-semibold" style="color:#9a3412;" data-question-report-other-hint>${otherHint}</p>
        </div>
    `;
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
    const checkboxes = questionReportIssueCheckboxes();

    return `
        <div class="js-question-report-block rounded-2xl border" style="${wrapperStyles} border-color:#fed7aa;background:#fff7ed;"
             data-question-id="${html(questionId)}"
             data-question-uuid="${html(questionUuid)}"
             data-question-text="${html(questionText)}">
            <div class="flex flex-wrap items-center justify-between gap-2 px-4 py-3">
                <div>
                    <div class="text-[10px] font-extrabold uppercase tracking-[0.16em]" style="color:#c2410c;">${html(questionReportUi('admin_only', 'Admin only'))}</div>
                    <div class="mt-1 flex flex-wrap items-center gap-2 text-sm font-semibold" style="color:#7c2d12;">
                        <span>${html(questionReportUi('admin_panel_title', 'Question report'))}</span>
                        <span class="hidden rounded-full bg-amber-200 px-2 py-0.5 text-[11px] font-extrabold uppercase tracking-[0.12em]" style="color:#7c2d12;" data-question-report-badge></span>
                    </div>
                </div>
                <button type="button"
                    class="rounded-xl border px-3 py-2 text-xs font-extrabold uppercase tracking-[0.12em] transition hover:bg-orange-100"
                    style="border-color:#fdba74;color:#9a3412;"
                    data-question-report-toggle>
                    ${html(questionReportUi('toggle', 'Report'))}
                </button>
            </div>
            <div class="border-t" style="border-color:#fed7aa;" data-question-report-existing></div>
            <form class="hidden border-t px-4 pb-4 pt-3" style="border-color:#fed7aa;" data-question-report-form>
                ${checkboxes}
                <label class="block text-xs font-bold uppercase tracking-[0.14em]" style="color:#9a3412;">
                    ${html(questionReportUi('comment_label', 'Comment'))}
                </label>
                <p class="mt-1 text-xs" style="color:#b45309;">${html(questionReportUi('comment_optional', 'Comment is optional when an issue type is selected'))}</p>
                <textarea
                    class="mt-2 min-h-[84px] w-full rounded-xl border px-3 py-2 text-sm leading-6 focus:outline-none focus:ring-2"
                    style="border-color:#fdba74;--tw-ring-color:#fb923c;"
                    name="comment"
                    maxlength="4000"
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

function questionReportLookupKeys(block) {
    const keys = [];
    const id = (block?.dataset?.questionId || '').trim();
    const uuid = (block?.dataset?.questionUuid || '').trim();
    if (id) keys.push(id);
    if (uuid && uuid !== id) keys.push(uuid);

    return keys;
}

function questionReportFindExisting(block) {
    const map = window.__QUESTION_REPORT_BY_QUESTION__;
    if (!map || typeof map !== 'object') {
        return [];
    }

    const seen = new Set();
    const collected = [];
    for (const key of questionReportLookupKeys(block)) {
        const list = map[key];
        if (!Array.isArray(list)) continue;
        for (const card of list) {
            if (!card || typeof card !== 'object') continue;
            const id = String(card.id || '');
            if (id === '' || seen.has(id)) continue;
            seen.add(id);
            collected.push(card);
        }
    }

    return collected;
}

function questionReportIssueLabel(slug) {
    const entry = questionReportIssueCatalog().find((item) => item?.key === slug || item?.slug === slug);

    return entry ? String(entry.label || slug) : String(slug);
}

function questionReportFormatDate(iso) {
    if (!iso) return '';
    const d = new Date(iso);
    if (Number.isNaN(d.getTime())) return String(iso);
    const pad = (n) => String(n).padStart(2, '0');

    return `${pad(d.getDate())}.${pad(d.getMonth() + 1)}.${d.getFullYear()} ${pad(d.getHours())}:${pad(d.getMinutes())}`;
}

function createQuestionReportExistingPanel(reports) {
    if (!Array.isArray(reports) || reports.length === 0) {
        return null;
    }

    const root = document.createElement('div');
    root.className = 'px-4 py-3';
    root.dataset.questionReportExistingInner = '1';

    const heading = document.createElement('div');
    heading.className = 'text-[10px] font-extrabold uppercase tracking-[0.16em]';
    heading.style.color = '#9a3412';
    heading.textContent = questionReportUi('admin_panel_title', 'Question report');
    root.appendChild(heading);

    const list = document.createElement('ul');
    list.className = 'mt-2 space-y-2';

    reports.forEach((card) => {
        const item = document.createElement('li');
        item.className = 'rounded-xl border px-3 py-2';
        item.style.borderColor = '#fdba74';
        item.style.background = '#ffedd5';

        const issueTypes = Array.isArray(card.issue_types)
            ? card.issue_types
            : (Array.isArray(card.issues) ? card.issues : []);
        const issueLabels = Array.isArray(card.issue_labels) ? card.issue_labels : [];
        const chips = document.createElement('div');
        chips.className = 'flex flex-wrap items-center gap-1.5';

        if (issueTypes.length > 0 || issueLabels.length > 0) {
            const labels = issueLabels.length > 0
                ? issueLabels
                : issueTypes.map((slug) => questionReportIssueLabel(slug));
            labels.forEach((label) => {
                const chip = document.createElement('span');
                chip.className = 'inline-flex items-center rounded-full bg-orange-200 px-2 py-0.5 text-[11px] font-bold';
                chip.style.color = '#7c2d12';
                chip.textContent = String(label || '');
                chips.appendChild(chip);
            });
        } else {
            const chip = document.createElement('span');
            chip.className = 'inline-flex items-center rounded-full bg-orange-100 px-2 py-0.5 text-[11px] font-bold';
            chip.style.color = '#9a3412';
            chip.textContent = questionReportUi('no_issue_type', 'Issue type not specified');
            chips.appendChild(chip);
        }

        item.appendChild(chips);

        const comment = String(card.comment || '').trim();
        const commentBlock = document.createElement('div');
        commentBlock.className = 'mt-1 whitespace-pre-line text-sm';
        commentBlock.style.color = comment ? '#7c2d12' : '#9a3412';
        commentBlock.textContent = comment || questionReportUi('no_comment', 'No comment');
        item.appendChild(commentBlock);

        const meta = [
            questionReportFormatDate(card.reported_at),
            card?.seeder?.class ? String(card.seeder.class) : '',
        ].filter(Boolean).join(' · ');

        if (meta) {
            const metaNode = document.createElement('div');
            metaNode.className = 'mt-1 text-[11px] uppercase tracking-[0.14em]';
            metaNode.style.color = '#9a3412';
            metaNode.textContent = meta;
            item.appendChild(metaNode);
        }

        list.appendChild(item);
    });

    root.appendChild(list);

    return root;
}

function renderQuestionReportExistingForBlock(block) {
    if (!block) return;
    const slot = block.querySelector('[data-question-report-existing]');
    if (!slot) return;

    const reports = questionReportFindExisting(block);
    slot.replaceChildren();

    const panel = createQuestionReportExistingPanel(reports);
    if (panel) {
        slot.appendChild(panel);
    }

    const badge = block.querySelector('[data-question-report-badge]');
    if (badge) {
        badge.textContent = questionReportUi('admin_badge', 'Has report');
        badge.classList.toggle('hidden', reports.length === 0);
    }

    const wrapper = block.closest('article[data-idx]')
        || block.closest('.drag-quiz__row')
        || block.closest('.match-card');
    if (wrapper) {
        if (reports.length > 0) {
            wrapper.dataset.questionReported = '1';
        } else {
            delete wrapper.dataset.questionReported;
        }
    }
}

function questionReportRecordLocally(report) {
    if (!report || typeof report !== 'object') return;
    const map = window.__QUESTION_REPORT_BY_QUESTION__ = window.__QUESTION_REPORT_BY_QUESTION__ || {};
    const card = {
        id: report.id || null,
        status: report.status || 'open',
        reported_at: report.reported_at || new Date().toISOString(),
        comment: report.comment || '',
        issue_types: Array.isArray(report.issue_types) ? report.issue_types : (Array.isArray(report.issues) ? report.issues : []),
        issue_labels: Array.isArray(report.issue_labels) ? report.issue_labels : [],
        seeder: report.seeder || {},
        test: report.test || {},
    };
    const keys = [String(report.question_id || ''), String(report.question_uuid || '')]
        .filter((key) => key !== '');
    for (const key of keys) {
        const list = map[key] = map[key] || [];
        if (!list.some((existing) => existing.id === card.id)) {
            list.push(card);
        }
    }
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
        // Refresh the inline existing-reports panel + container highlight even
        // if the block itself was already injected on a prior pass.
        const existingBlock = Array.from(container.children).find((child) => child.classList?.contains('js-question-report-block'));
        renderQuestionReportExistingForBlock(existingBlock);
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
        renderQuestionReportExistingForBlock(block);
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
    const issueTypes = Array.from(form.querySelectorAll('[data-question-report-issue]:checked'))
        .map((input) => String(input.value || '').trim())
        .filter(Boolean);

    return {
        question_id: block.dataset.questionId || null,
        question_uuid: block.dataset.questionUuid || null,
        issue_types: issueTypes,
        comment: String(formData.get('comment') || '').trim(),
        test_slug: window.__QUESTION_REPORT_TEST_SLUG__ || null,
        test_name: window.__QUESTION_REPORT_TEST_NAME__ || null,
        mode: window.__QUESTION_REPORT_MODE__ || null,
        url: questionReportCurrentUrl(),
    };
}

function questionReportCurrentUrl() {
    try {
        const url = new URL(window.location.href);
        url.searchParams.delete('filters');

        return url.toString();
    } catch (error) {
        return window.location.href;
    }
}

function updateQuestionReportOtherHint(form) {
    if (!form) return;
    const hint = form.querySelector('[data-question-report-other-hint]');
    if (!hint) return;

    const otherSelected = Boolean(form.querySelector('[data-question-report-issue][value="other"]:checked'));
    hint.classList.toggle('hidden', !otherSelected);
}

async function submitQuestionReport(form) {
    const block = form.closest('.js-question-report-block');
    const status = block?.querySelector('[data-question-report-status]');
    const submitButton = form.querySelector('[data-question-report-submit]');
    const payload = questionReportPayload(block, form);

    if (payload.issue_types.length === 0 && !payload.comment) {
        if (status) {
            status.textContent = questionReportUi('validation.issue_or_comment_required', 'Choose at least one issue type or write a comment');
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
        updateQuestionReportOtherHint(form);
        if (status) {
            status.textContent = `${questionReportUi('success', questionReportUi('saved', 'Saved'))}: ${data?.report?.file || ''}`;
        }
        if (block) {
            block.dataset.reported = '1';
            questionReportRecordLocally(data?.report);
            renderQuestionReportExistingForBlock(block);
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

    document.addEventListener('change', (event) => {
        const issueInput = event.target.closest('[data-question-report-issue]');
        if (!issueInput) {
            return;
        }

        updateQuestionReportOtherHint(issueInput.closest('[data-question-report-form]'));
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
