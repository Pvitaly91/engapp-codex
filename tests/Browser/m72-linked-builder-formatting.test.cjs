'use strict';
// Native linked-builder formatting only: no browser, Laravel, SQL, working .env,
// network, file writes or formatter replacement. Default reads the frozen active audit.
// Pre-freeze proof requires M72_LINKED_AUDIT_DRAFT=1, the exact private relative
// M72_LINKED_AUDIT path below and an independently reviewed M72_LINKED_AUDIT_SHA.
const {test, after} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const crypto = require('node:crypto');

const root = path.resolve(__dirname, '../..');
const read = relative => fs.readFileSync(path.join(root, relative), 'utf8');
const sha = relative => crypto.createHash('sha256').update(fs.readFileSync(path.join(root, relative))).digest('hex');
const lfSha = relative => crypto.createHash('sha256').update(read(relative).replace(/\r\n/g, '\n')).digest('hex');
const fixturePath = 'tests/Fixtures/m72-owned-source-hunks.json';
assert.equal(sha(fixturePath), 'c4a27b89f25a97bd4a0596ac061285843dbef1d638c5d8a3f752e28b17493134',
    'independent accepted-base fixture, not a newly harvested formatter pin');
const fixture = JSON.parse(read(fixturePath));
assert.equal(fixture.base_sha, '2bcd468b25af3c44dcf34ccee0e012f3f683335d');
const bladePath = 'resources/views/components/text-block-practice-questions.blade.php';
const bladePin = fixture.visual_sources.filter(file => file.path === bladePath);
assert.equal(bladePin.length, 1);
assert.equal(bladePin[0].base_lf_sha256, 'c224042fcc5fe767064a5b175369c020de4cb61ecfbdf7f73f9e5679385795f6');
assert.equal(lfSha(bladePath), bladePin[0].base_lf_sha256, 'entire unchanged actual Blade formatter source');

const presentationPath = 'app/Support/M72BuilderSelectionPresentation.php';
const presentation = read(presentationPath);
function constant(name) {
    const matches = [...presentation.matchAll(new RegExp("public const " + name + " = '([^']+)';", 'g'))];
    assert.equal(matches.length, 1, 'one active presentation constant: ' + name);
    return matches[0][1];
}
const activePath = constant('AUDIT');
assert.ok(!process.env.M72_LINKED_AUDIT_DRAFT || process.env.M72_LINKED_AUDIT_DRAFT === '1');
const draft = process.env.M72_LINKED_AUDIT_DRAFT === '1';
const auditPath = process.env.M72_LINKED_AUDIT || activePath;
if (draft) assert.equal(auditPath, 'storage/app/seo-m72-local/bank-audit-revision-draft-v1.json');
else assert.match(auditPath, /^docs\/content\/m72-partitives-negatives-geography-bank-audit\.v1\.0\.1\.json$/);
const auditSha = !draft && auditPath === activePath ? constant('AUDIT_SHA') : process.env.M72_LINKED_AUDIT_SHA;
assert.match(auditSha || '', /^[a-f0-9]{64}$/, 'explicit alternate audit requires its reviewed SHA-256');
assert.equal(sha(auditPath), auditSha, 'frozen active bank-only revision, exact raw bytes');
const audit = JSON.parse(read(auditPath));
assert.equal(audit.status, draft ? 'private-draft-not-frozen' : 'frozen');
assert.equal(audit.version, '1.0.1');
assert.equal(audit.package, 'm72-partitives-negatives-geography-bank-audit');
assert.equal(audit.targets.length, 3);

const oldPath = 'docs/content/m72-partitives-negatives-geography-bank-audit.v1.json';
assert.equal(sha(oldPath), 'bc51862d48ec3764363574881b14fdc78f0b481730a3a72bd86b9f92264e2c1d',
    'historical 1.0.0 source stays immutable; negative control is not invented');
const oldAudit = JSON.parse(read(oldPath));
const old32781 = oldAudit.targets.flatMap(target => target.first_five).filter(question => question.id === 32781);
assert.equal(old32781.length, 1);

const blade = read(bladePath).replace(/\r\n/g, '\n');
const begin = '                composeCorrectText() {';
const end = '                normalizeAnswer(value) {';
assert.equal(blade.split(begin).length - 1, 1);
assert.equal(blade.split(end).length - 1, 1);
const from = blade.indexOf(begin);
const to = blade.indexOf(end, from);
assert.ok(to > from && to - from < 5000, 'bounded contiguous exact native method region');
const methods = blade.slice(from, to);
assert.doesNotMatch(methods, /@(?:php|json|js|endphp)\b|\{\{!!?/);
assert.equal([...methods.matchAll(/^ {16}[A-Za-z]+\(/gm)].length, 4,
    'only correct/selected formatter entry points and both shared native methods');
let networkCalls = 0;
const native = new vm.Script('({' + methods + '})', {filename: bladePath + ':native-compose-methods'})
    .runInNewContext({fetch() {networkCalls++; throw Error('Network forbidden');}}, {timeout: 1000});
assert.deepEqual(Object.keys(native), [
    'composeCorrectText', 'composeSelectedText', 'composeTextFromTokens', 'composePunctuation',
]);

const emptyListHash = '4f53cda18c2baa0c0354bb5f9a3ecbe5ed12ab4d8e11ba873c2f11161202b945';
function payload(question) {
    assert.equal(question.snapshot_hashes.hints_snapshot, emptyListHash,
        'no compose_prompt hint: actual native authored_compose is false');
    assert.equal(typeof question.visible_condition_uk, 'string');
    assert.match(question.visible_condition_uk, /[А-Яа-яІіЇїЄєҐґ]/u);
    return {
        id: question.id, type: '4', question: question.visible_condition_uk,
        authored_compose: false, compose_punctuation: null,
        correct_tokens: [...question.correct_tokens],
    };
}
function model(question) {
    return {
        ...native,
        currentQuestion: payload(question),
        selectedTokens: question.correct_tokens.map((value, index) => ({id: index + '-' + value, value})),
    };
}
const ids = [
    [32115, 32120, 32122, 32123, 32124],
    [32775, 32778, 32779, 32780, 32788],
    [31539, 31542, 31543, 31547, 31550],
];
const protectedFiles = [fixturePath, bladePath, presentationPath, auditPath, oldPath];
const before = Object.fromEntries(protectedFiles.map(relative => [relative, sha(relative)]));
const rows = audit.targets.flatMap((target, index) => {
    assert.equal(target.level, null, 'unchanged actual technical filter');
    assert.deepEqual(target.expected_selected_ids, ids[index]);
    assert.deepEqual(target.first_five.map(question => question.id), ids[index]);
    return target.first_five;
});
assert.equal(rows.length, 15);
assert.equal(new Set(rows.map(question => question.id)).size, 15);
assert.equal(rows.some(question => question.id === 32781), false,
    'formatter-unsafe old candidate is excluded from active selection, not repaired in the engine');

test('four exact native methods come from the complete independently pinned unchanged Blade', () => {
    assert.equal(lfSha(bladePath), bladePin[0].base_lf_sha256);
    assert.equal(networkCalls, 0);
});

for (const question of rows) {
    test(question.id + ': actual native correct and selected text retain the canonical sentence and period', () => {
        assert.ok(Array.isArray(question.correct_tokens) && question.correct_tokens.length > 0);
        assert.equal(question.correct_tokens.join(' '), question.expected_word_key,
            'canonical ordered native word key, without normalization or re-casing');
        assert.doesNotMatch(question.expected_word_key, /[?.!]$/);
        const expected = question.expected_word_key + '.';
        const instance = model(question);
        assert.equal(instance.composePunctuation(question.expected_word_key), '.',
            'these fifteen reviewed tasks are declarative sentences');
        assert.equal(instance.composeCorrectText(), expected,
            'real native correct-answer presentation must not split internal no');
        assert.equal(instance.composeSelectedText(), expected,
            'real native assembled-answer presentation must match the canonical ordered key exactly');
        assert.deepEqual(instance.currentQuestion.correct_tokens, question.correct_tokens);
        assert.deepEqual(instance.selectedTokens.map(token => token.value), question.correct_tokens);
    });
}

test('negative control: unchanged native formatter really breaks historical ID32781; no silent repair', () => {
    const question = old32781[0];
    assert.equal(question.expected_word_key, 'I saw no mistakes in your answer');
    assert.deepEqual(question.correct_tokens, ['I', 'saw', 'no', 'mistakes', 'in', 'your', 'answer']);
    const instance = model(question);
    const observed = 'I saw? No, mistakes in your answer.';
    assert.equal(instance.composeCorrectText(), observed);
    assert.equal(instance.composeSelectedText(), observed);
    assert.notEqual(observed, question.expected_word_key + '.');
    assert.deepEqual(instance.currentQuestion.correct_tokens, question.correct_tokens);
});

after(() => {
    for (const relative of protectedFiles) assert.equal(sha(relative), before[relative], 'no source writes: ' + relative);
    assert.equal(networkCalls, 0);
    console.log(JSON.stringify({
        source_mode: draft ? 'reviewed-private-draft-not-frozen' : 'frozen-active-audit',
        audit_path: auditPath, audit_sha256: auditSha, selected_questions: 15,
        actual_correct_and_selected_formatted_strings: 30,
        historical_bad_candidate: 32781, historical_bad_output: 'I saw? No, mistakes in your answer.',
        replacement_existing_candidate: 32788, unchanged_blade_lf_sha256: bladePin[0].base_lf_sha256,
        network_calls: networkCalls, db_access: false, file_writes: 0,
        boundary: 'Offline exact native formatting of reviewed selected payload fields; real browser/DOM acceptance is separate.',
    }));
});
