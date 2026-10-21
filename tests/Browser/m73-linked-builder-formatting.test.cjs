'use strict';
// Offline execution of the FOUR ACTUAL unchanged Blade methods, not a browser or
// replacement formatter. No Laravel, .env, SQL, HTTP, network or file writes.
// Default: node --test tests/Browser/m73-linked-builder-formatting.test.cjs
// Pre-freeze explicit reviewed private bundle:
// node tests/Browser/m73-linked-builder-formatting.test.cjs --draft-assembly
//   storage/app/seo-m73-local/bank-assembly-draft-v1.json --sha256 <reviewed-raw-SHA>
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
const fixturePath = 'tests/Fixtures/m73-owned-source-hunks.json';
const fixtureSha = 'a63f7829d4d0009b8ce615af46ced46a2065659781df8834abbcb7177917bf0a'; // Independent accepted-M72-base fixture receipt.
assert.match(fixtureSha, /^[a-f0-9]{64}$/, 'M73 accepted-base fixture must have an independently supplied final raw SHA');
assert.equal(sha(fixturePath), fixtureSha, 'independently captured accepted M72 base fixture; never harvest a formatter pin here');
const fixture = JSON.parse(read(fixturePath));
assert.equal(fixture.base_sha, 'cb55b329827e52a56cb6d41edb21e70768cf86f4');
const bladePath = 'resources/views/components/text-block-practice-questions.blade.php';
const casePath = 'app/Support/ComposeTokenCase.php';
const bladePin = fixture.visual_sources.filter(file => file.path === bladePath);
assert.equal(bladePin.length, 1);
assert.equal(bladePin[0].base_lf_sha256, 'c224042fcc5fe767064a5b175369c020de4cb61ecfbdf7f73f9e5679385795f6');
assert.equal(lfSha(bladePath), bladePin[0].base_lf_sha256, 'entire actual unchanged Blade source, not just extracted methods');
assert.equal(sha(casePath), '8cf7d1ad3972b571285b7d621f23d87e7efaf3cd76126b4843b7d6ae85be7923', 'actual unchanged native case source');

const presentationPath = 'app/Support/M73BuilderSelectionPresentation.php';
const cli = process.argv.slice(2);
const draft = cli.length !== 0;
let auditPath, auditSha, audit;
const protectedFiles = [fixturePath, bladePath, casePath];
if (draft) {
    assert.equal(cli.length, 4, 'private input requires explicit path AND independently reviewed raw SHA');
    assert.equal(cli[0], '--draft-assembly'); assert.equal(cli[2], '--sha256');
    auditPath = cli[1]; auditSha = cli[3];
    assert.match(auditPath, /^storage\/app\/seo-m73-local\/bank-assembly-draft-[A-Za-z0-9._-]+\.json$/);
    assert.match(auditSha, /^[a-f0-9]{64}$/);
    assert.equal(sha(auditPath), auditSha, 'exact explicitly reviewed private assembly bytes');
    const bundle = JSON.parse(read(auditPath));
    assert.equal(bundle.status, 'private-assembly-not-frozen-not-activated');
    audit = bundle.audit;
    assert.equal(audit.status, 'private-draft-not-frozen');
} else {
    const presentation = read(presentationPath);
    function constant(name) {
        const matches = [...presentation.matchAll(new RegExp("public const " + name + " = '([^']+)';", 'g'))];
        assert.equal(matches.length, 1, 'one trusted presentation constant: ' + name);
        return matches[0][1];
    }
    auditPath = constant('AUDIT'); auditSha = constant('AUDIT_SHA');
    assert.equal(auditPath, 'docs/content/m73-generic-irregular-collective-bank-audit.v1.json');
    assert.match(auditSha, /^[a-f0-9]{64}$/);
    assert.equal(sha(auditPath), auditSha, 'frozen active finite M73 audit exact raw bytes');
    audit = JSON.parse(read(auditPath));
    assert.equal(audit.status, 'frozen'); protectedFiles.push(presentationPath);
}
protectedFiles.push(auditPath);
assert.equal(audit.package, 'm73-generic-irregular-collective-bank-audit');
assert.equal(audit.version, '1.0.0'); assert.equal(audit.locale, 'uk');
assert.equal(audit.independent_before_sha256, '41cb3ac163738e4d2662b4c53a3b2288de3303161d2da6ef5a1f42a9632d0ac9');
assert.deepEqual(audit.review_summary.technical_filter, [null, 'A2', 'B1']);
assert.deepEqual(audit.review_summary.intended_selected_levels, ['B2', 'A2', 'B1']);
assert.equal(audit.review_summary.actual_filtered_pool, 96);
assert.equal(audit.review_summary.candidate_semantic_review, 36);
assert.equal(audit.review_summary.scope_excluded_without_semantic_review, 60);
assert.equal(audit.targets.length, 3);

const historicalPath = 'docs/content/m72-partitives-negatives-geography-bank-audit.v1.json';
assert.equal(sha(historicalPath), 'bc51862d48ec3764363574881b14fdc78f0b481730a3a72bd86b9f92264e2c1d', 'immutable historical real negative control, not an invented key');
protectedFiles.push(historicalPath);
const historical = JSON.parse(read(historicalPath));
const old32781 = historical.targets.flatMap(target => target.first_five).filter(row => row.id === 32781);
assert.equal(old32781.length, 1);

const blade = read(bladePath).replace(/\r\n/g, '\n');
const begin = '                composeCorrectText() {';
const end = '                normalizeAnswer(value) {';
assert.equal(blade.split(begin).length - 1, 1); assert.equal(blade.split(end).length - 1, 1);
const from = blade.indexOf(begin); const to = blade.indexOf(end, from);
assert.ok(to > from && to - from < 5000, 'bounded exact native contiguous method region');
const methods = blade.slice(from, to);
assert.doesNotMatch(methods, /@(?:php|json|js|endphp)\b|\{\{!!?/);
assert.equal([...methods.matchAll(/^ {16}[A-Za-z]+\(/gm)].length, 4);
let networkCalls = 0;
const native = new vm.Script('({' + methods + '})', {filename: bladePath + ':actual-native-methods'})
    .runInNewContext({fetch() {networkCalls++; throw Error('Network forbidden');}}, {timeout: 1000});
assert.deepEqual(Object.keys(native), ['composeCorrectText', 'composeSelectedText', 'composeTextFromTokens', 'composePunctuation']);
const emptyListHash = '4f53cda18c2baa0c0354bb5f9a3ecbe5ed12ab4d8e11ba873c2f11161202b945';
function model(question) {
    assert.equal(question.snapshot_hashes.hints_snapshot, emptyListHash, 'no authored compose_prompt; unchanged native path remains authored_compose=false');
    assert.match(question.visible_condition_uk, /[А-Яа-яІіЇїЄєҐґ]/u);
    return {
        ...native,
        currentQuestion: {id: question.id, type: '4', question: question.visible_condition_uk, authored_compose: false,
            compose_punctuation: null, correct_tokens: [...question.correct_tokens]},
        selectedTokens: question.correct_tokens.map((value, index) => ({id: index + '-' + value, value})),
    };
}
const ids = [[31638,31640,31641,31642,31643],[32548,32551,32554,32555,32557],[32631,32632,32634,32639,32641]];
// Independent finite editorial expectations. Do not derive expected strings from the formatter output.
const expected = new Map([
    [31638, ['The elderly have influence.', 'Літні люди мають вплив.']],
    [31640, ['The wolf is a protected species.', 'Вовк є видом, що перебуває під охороною.']],
    [31641, ['People need reliable information.', 'Людям потрібна надійна інформація.']],
    [31642, ['A wolf can represent a whole class.', 'Один вовк може представляти цілий клас.']],
    [31643, ['The wolf in the reserve looked calm.', 'Вовк у заповіднику виглядав спокійним.']],
    [32548, ['These sheep are very quiet.', 'Ці вівці дуже тихі.']],
    [32551, ['Two children are playing in the yard.', 'У дворі граються двоє дітей.']],
    [32554, ['Two women work in this office.', 'Дві жінки працюють у цьому офісі.']],
    [32555, ['Many people came to the meeting.', 'На зустріч прийшло багато людей.']],
    [32557, ['My feet hurt after the long walk.', 'Мої ступні болять після довгої прогулянки.']],
    [32631, ['The club won and it celebrated as one group.', 'Клуб переміг, і він святкував як одна група.']],
    [32632, ['The staff finished work and they went home separately.', 'Працівники закінчили роботу, і вони пішли додому окремо.']],
    [32634, ['A group of children is playing near the gate.', 'Група дітей грається біля воріт.']],
    [32639, ['The team is ready as one unit.', 'Команда готова як єдине ціле.']],
    [32641, ['The committee has made one decision.', 'Комітет ухвалив одне рішення.']],
]);
const before = Object.fromEntries(protectedFiles.map(relative => [relative, sha(relative)]));
const rows = audit.targets.flatMap((target, index) => {
    assert.equal(target.level, [null,'A2','B1'][index]);
    assert.deepEqual(target.expected_selected_ids, ids[index]);
    assert.deepEqual(target.first_five.map(row => row.id), ids[index]);
    assert.equal(target.all_pool_snapshots.length, [72,12,12][index]);
    assert.equal(target.all_eligible_review.length, 12);
    assert.equal(target.scope_exclusions.length, [60,0,0][index]);
    return target.first_five;
});
assert.equal(rows.length, 15); assert.equal(new Set(rows.map(row => row.id)).size, 15);
assert.deepEqual(rows.map(row => row.id), ids.flat());
for (const id of [32781,32633,32636,32642]) assert.equal(rows.some(row => row.id === id), false, 'unsafe/off-topic reserve is not selected: ' + id);

test('four actual native methods and native case source remain completely unchanged from accepted M72', () => {
    assert.equal(lfSha(bladePath), bladePin[0].base_lf_sha256);
    assert.equal(sha(casePath), '8cf7d1ad3972b571285b7d621f23d87e7efaf3cd76126b4843b7d6ae85be7923');
    assert.equal(networkCalls, 0);
});

for (const question of rows) {
    test(question.id + ': actual native correct/assembled text equals reviewed full sentence and exact UK translation', () => {
        assert.equal(question.is_new, false);
        assert.equal(question.snapshot_hashes.hints_snapshot, emptyListHash);
        assert.equal(question.snapshot_hashes.verb_hints_snapshot, emptyListHash);
        assert.equal(question.variants_sha256, emptyListHash);
        assert.deepEqual(question.token_presentations, []); assert.deepEqual(question.option_presentations, []);
        assert.deepEqual(question.original_ordered_instances.map(row => row.option), question.raw_correct_tokens);
        assert.equal(question.correct_tokens.join(' '), question.expected_word_key, 'unchanged canonical native ordering/case');
        assert.doesNotMatch(question.expected_word_key, /[?.!]$/);
        const [sentence, translation] = expected.get(question.id);
        assert.equal(question.expected_word_key + '.', sentence);
        assert.equal(question.translation_uk, translation);
        const instance = model(question);
        assert.equal(instance.composePunctuation(question.expected_word_key), '.');
        assert.equal(instance.composeCorrectText(), sentence, 'actual correct-answer display, not a score assertion');
        assert.equal(instance.composeSelectedText(), sentence, 'actual assembled-answer display, not a hidden-key substitution');
        assert.deepEqual(instance.currentQuestion.correct_tokens, question.correct_tokens);
        assert.deepEqual(instance.selectedTokens.map(token => token.value), question.correct_tokens);
        for (const token of question.correct_tokens) {
            assert.notEqual(token.trim(), ''); assert.doesNotMatch(token, /^(?:ø|без артикля|no article)$/iu);
            assert.ok(question.token_bank_with_multiplicity.filter(value => value === token).length >= question.correct_tokens.filter(value => value === token).length,
                'every repeated correct-token instance exists in the unchanged token bank');
        }
    });
}

test('generic conditions state fictional protection, metalinguistic class and known repeated determiners', () => {
    const byId = new Map(rows.map(row => [row.id,row]));
    assert.match(byId.get(31640).visible_condition_uk, /вигаданому/u);
    assert.match(byId.get(31640).visible_condition_uk, /не твердження про реальний правовий статус/u);
    assert.match(byId.get(31642).visible_condition_uk, /не біологічний таксономічний ранг/u);
    assert.equal(byId.get(31642).raw_correct_tokens.filter(token => token === 'a').length, 2);
    assert.equal(byId.get(31643).raw_correct_tokens.filter(token => token === 'the').length, 2);
    assert.match(byId.get(32555).visible_condition_uk, /повсякден/u);
    assert.match(byId.get(32639).visible_condition_uk, /однин/u);
    assert.match(byId.get(32641).visible_condition_uk, /однин/u);
});

test('negative control historical M72 ID32781 still breaks visibly; M73 does not repair or select it', () => {
    const question = old32781[0];
    assert.equal(question.expected_word_key, 'I saw no mistakes in your answer');
    assert.deepEqual(question.correct_tokens, ['I','saw','no','mistakes','in','your','answer']);
    const instance = model(question); const observed = 'I saw? No, mistakes in your answer.';
    assert.equal(instance.composeCorrectText(), observed); assert.equal(instance.composeSelectedText(), observed);
    assert.notEqual(observed, question.expected_word_key + '.');
    assert.deepEqual(instance.currentQuestion.correct_tokens, question.correct_tokens);
});

for (const [id, tokens, correct] of [
    [32633, ['A','group','of','Students','is','waiting','outside'], 'A group of students is waiting outside.'],
    [32636, ['A','crowd','of','Tourists','is','near','the','museum'], 'A crowd of tourists is near the museum.'],
]) {
    test('negative control ' + id + ': source internal capital remains visible and is excluded, not recased', () => {
        const target = audit.targets[2]; const candidates = target.all_eligible_review.filter(row => row.id === id);
        assert.equal(candidates.length, 1); const review = candidates[0];
        assert.equal(review.selected, false); assert.equal(review.semantic_verdict, 'excluded-visible-internal-capital-defect');
        assert.ok(target.excluded_question_ids.includes(id));
        assert.equal(review.native_case_word_key, tokens.join(' '));
        assert.deepEqual(review.raw_correct_tokens, ['a', ...tokens.slice(1)]);
        const question = {id, visible_condition_uk: review.visible_condition_uk, correct_tokens: tokens,
            snapshot_hashes: {hints_snapshot: emptyListHash}};
        const instance = model(question); const actual = tokens.join(' ') + '.';
        assert.equal(instance.composeCorrectText(), actual); assert.equal(instance.composeSelectedText(), actual);
        assert.notEqual(actual, correct, 'score-equal native output is not a grammatical visible sentence');
        assert.deepEqual(instance.currentQuestion.correct_tokens, tokens);
    });
}

after(() => {
    for (const relative of protectedFiles) assert.equal(sha(relative), before[relative], 'no source writes: ' + relative);
    assert.equal(networkCalls, 0);
    console.log(JSON.stringify({source_mode: draft ? 'explicit-reviewed-private-assembly' : 'frozen-active-audit',
        audit_path: auditPath, audit_sha256: auditSha, selected_questions: 15, actual_correct_and_selected_strings: 30,
        actual_pool: 96, semantic_reviews: 36, scope_only_exclusions: 60,
        negative_controls: [32781,32633,32636], unchanged_blade_lf_sha256: bladePin[0].base_lf_sha256,
        network_calls: networkCalls, database_access: false, file_writes: 0,
        boundary: 'Offline actual native method execution; real browser DOM acceptance remains a separate required main-task step.'}));
});
